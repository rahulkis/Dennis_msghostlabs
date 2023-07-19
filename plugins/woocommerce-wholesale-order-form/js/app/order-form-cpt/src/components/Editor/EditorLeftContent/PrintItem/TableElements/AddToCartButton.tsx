import {
  MutableRefObject,
  useRef,
  useEffect,
  useState,
  createRef,
} from "react";
import { Button } from "antd";
import { connect } from "react-redux";
import { bindActionCreators, Dispatch } from "redux";

// Helper
import { getRowsHeight } from "helpers/getRowsHeight";
import DisplayVariationDropdown from "./shared/DisplayVariationDropdown";

import { productActions } from "store/actions";

const { loadMoreVariations } = productActions;

const AddToCartButton = (props: any) => {
  const {
    orderForm,
    getPropValue,
    properties,
    products,
    hoveredRow,
    setHoveredRow,
    onMouseEnter,
    onMouseLeave,
    headerHeight,
    setHeaderHeight,
    useStyleValue,
    addToCartButtonStrings,
    actions,
  } = props;

  const { loadMoreVariations } = actions;

  const { column_heading, button_text } = addToCartButtonStrings;

  const [columnRefs, setColumnRefs] = useState([]);
  const [columnRows, setColumnRows] = useState([]);

  const [variationSelector, setVariationSelector] = useState(
    getPropValue({
      properties,
      prop: "variationSelectorStyle",
    }) ?? "combo"
  );

  const [displayVariationDropdown, setDisplayVariationDropdown] = useState(
    getPropValue({
      properties,
      prop: "displayVariationDropdown",
    }) ?? false
  );

  // Re-render when rows are updated
  const [, setForceRender] = useState([]);

  const headerText =
    getPropValue({ properties, prop: "columnHeading" }) ?? column_heading;

  let fontSize = useStyleValue(
    getPropValue({
      properties,
      prop: "fontSize",
    }) ?? ""
  );

  let width = useStyleValue(
    getPropValue({
      properties,
      prop: "width",
    }) ?? ""
  );

  const addToCartAlignment =
    getPropValue({
      properties,
      prop: "justifyContent",
    }) ?? "center";

  const buttonColor =
    getPropValue({
      properties,
      prop: "buttonColor",
    }) ?? "";

  const buttonTextColor =
    getPropValue({
      properties,
      prop: "buttonTextColor",
    }) ?? "";

  // CSS
  const addToCartStyle = {
    display: "flex",
    justifyContent: "center",
    textAlign:
      addToCartAlignment === "flex-start"
        ? ("left" as const)
        : addToCartAlignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const addToCartElementStyle = {
    width,
    fontSize: fontSize === "auto" ? "inherit" : fontSize,
    border: buttonColor,
    background: buttonColor,
    color: buttonTextColor,
    paddingTop:
      getPropValue({
        properties,
        prop: "paddingTop",
      }) ?? 0,
    paddingRight:
      getPropValue({
        properties,
        prop: "paddingRight",
      }) ?? 0,
    paddingBottom:
      getPropValue({
        properties,
        prop: "paddingBottom",
      }) ?? 0,
    paddingLeft:
      getPropValue({
        properties,
        prop: "paddingLeft",
      }) ?? 0,
  };

  const targetRef = useRef(null) as unknown as MutableRefObject<HTMLDivElement>;

  useEffect(() => {
    if (
      targetRef.current.offsetHeight > 0 &&
      headerHeight < targetRef.current.offsetHeight
    )
      setHeaderHeight(targetRef.current.offsetHeight);
  }, [targetRef, headerText, headerHeight, setHeaderHeight]);

  // Variation Selector Style
  useEffect(() => {
    setVariationSelector(
      getPropValue({
        properties,
        prop: "variationSelectorStyle",
      })
    );
  }, [properties]);

  // Display Variation Dropdown
  useEffect(() => {
    setDisplayVariationDropdown(
      getPropValue({
        properties,
        prop: "displayVariationDropdown",
      })
    );
  }, [properties]);

  if (
    addToCartElementStyle.paddingTop === 0 &&
    addToCartElementStyle.paddingRight === 0 &&
    addToCartElementStyle.paddingBottom === 0 &&
    addToCartElementStyle.paddingLeft === 0
  ) {
    delete addToCartElementStyle.paddingTop;
    delete addToCartElementStyle.paddingRight;
    delete addToCartElementStyle.paddingBottom;
    delete addToCartElementStyle.paddingLeft;
  }

  useEffect(() => {
    if (products.products.length > 0) {
      setColumnRefs(products.products.map((p: any, i: any) => createRef()));
    }
  }, [products.products]);

  useEffect(() => {
    if (columnRefs.length && products.products.length > 0) {
      let tempRowsHeight: any = [];

      columnRefs.map((ref: any, i: any) => {
        let temp = null;

        if (ref.current !== null && ref.current.style !== null) {
          temp = ref.current.style.height;
          ref.current.style.height = "";
        }

        if (ref.current) {
          tempRowsHeight[i] = ref.current.offsetHeight;
        }

        if (temp !== null) ref.current.style.height = temp;

        return false;
      });

      setColumnRows(tempRowsHeight);
    }
  }, [columnRefs, products.products]);

  useEffect(() => {
    if (columnRows.length > 0) {
      let rowsHeight: any = localStorage.getItem("rowsHeight") ?? [];

      if (typeof rowsHeight === "string") {
        rowsHeight = JSON.parse(rowsHeight);
      }

      if (rowsHeight.length === 0) {
        localStorage.setItem("rowsHeight", JSON.stringify(columnRows));
      } else {
        let newCellsHeight: any = [];

        columnRows.map((height: any, i: any) => {
          if (typeof rowsHeight[i] !== "undefined" && height > rowsHeight[i]) {
            newCellsHeight[i] = height;
          } else if (typeof rowsHeight[i] === "undefined") {
            newCellsHeight[i] = height;
          } else {
            newCellsHeight[i] = rowsHeight[i];
          }
          return false;
        });
        localStorage.setItem("rowsHeight", JSON.stringify(newCellsHeight));
      }
      setForceRender(rowsHeight);
    }
  }, [columnRows]);

  return (
    <>
      <div
        className="heading"
        style={{
          ...addToCartStyle,
          height: headerHeight > 0 ? headerHeight + "px" : "",
          justifyContent: addToCartAlignment,
        }}
        ref={targetRef}
      >
        {headerText}
      </div>
      {products.products.map((d: any, i: any) => {
        let selected = d.id === parseInt(hoveredRow) ? "hovered" : "";
        let heightCSS: any = getRowsHeight({ i });
        let rowsHeight: any = localStorage.getItem("rowsHeight") ?? [];

        if (typeof rowsHeight === "string") {
          rowsHeight = JSON.parse(rowsHeight);
        }
        if (typeof rowsHeight[i] !== "undefined") {
          heightCSS = {
            height: rowsHeight[i] + "px",
          };
        }

        return (
          <div
            key={i}
            className={`row ${
              i % 2 === 0 ? "odd" : "even"
            } ${selected} row${i}`}
            style={{
              flexDirection: "column",
              ...addToCartStyle,
              ...heightCSS,
              alignItems: addToCartAlignment,
            }}
            data-productid={d.id}
            onMouseEnter={(e: any) => onMouseEnter({ e, setHoveredRow })}
            onMouseLeave={(e: any) => onMouseLeave({ e, setHoveredRow })}
            ref={columnRefs[i]}
          >
            {displayVariationDropdown === true ? (
              <DisplayVariationDropdown
                variationSelector={variationSelector}
                orderForm={orderForm}
                products={products}
                d={d}
                loadMoreVariations={loadMoreVariations}
              />
            ) : (
              <></>
            )}
            <Button
              size="large"
              type="primary"
              style={{ ...addToCartElementStyle, marginTop: "10px" }}
            >
              {getPropValue({ properties, prop: "buttonText" }) ?? button_text}
            </Button>
          </div>
        );
      })}
    </>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  addToCartButtonStrings: store.i18n.backend.table_elements.add_to_cart_button,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      loadMoreVariations,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(AddToCartButton);
