import {
  useRef,
  MutableRefObject,
  useEffect,
  createRef,
  useState
} from "react";
import { Avatar } from "antd";
import { connect } from "react-redux";

declare var WWOF_Backend_Options: any;

const ProductImage = (props: any) => {
  const {
    getPropValue,
    properties,
    products,
    hoveredRow,
    setHoveredRow,
    onMouseEnter,
    onMouseLeave,
    headerHeight,
    setHeaderHeight,
    productImageStrings
  } = props;

  const { column_heading } = productImageStrings;

  const [columnRefs, setColumnRefs] = useState([]);
  const [columnRows, setColumnRows] = useState([]);

  // Re-render when rows are updated
  const [, setForceRender] = useState([]);

  const headerText =
    getPropValue({ properties, prop: "columnHeading" }) || column_heading;

  const placeholder = WWOF_Backend_Options.product_image_placeholder;

  const imageSize = getPropValue({
    properties,
    prop: "imageSize"
  }) || { width: 48, height: 48 };

  const imageAlignment =
    getPropValue({
      properties,
      prop: "justifyContent"
    }) || "center";

  const imageStyle = {
    display: "flex",
    justifyContent: imageAlignment,
    textAlign:
      imageAlignment === "flex-start"
        ? ("left" as const)
        : imageAlignment === "flex-end"
        ? ("right" as const)
        : ("center" as const)
  };

  const targetRef = useRef(null) as unknown as MutableRefObject<HTMLDivElement>;

  useEffect(() => {
    if (
      targetRef.current.offsetHeight > 0 &&
      headerHeight < targetRef.current.offsetHeight
    )
      setHeaderHeight(targetRef.current.offsetHeight);
  }, [targetRef, headerText]);

  useEffect(() => {
    if (products.products.length > 0) {
      setColumnRefs(products.products.map((p: any, i: any) => createRef()));
    }
  }, [products.products]);

  useEffect(() => {
    if (columnRefs.length && products.products.length > 0) {
      let tempRowsHeight: any = [];

      columnRefs.forEach((ref: any, i: any) => {
        let temp = null;

        if (ref.current !== null && ref.current.style !== null) {
          temp = ref.current.style.height;
          ref.current.style.height = "";
        }

        if (ref.current) {
          tempRowsHeight[i] = ref.current.offsetHeight;
        }

        if (temp !== null) ref.current.style.height = temp;
      });

      setColumnRows(tempRowsHeight);
    }
  }, [columnRefs, products.products]);

  useEffect(() => {
    if (columnRows.length > 0) {
      let rowsHeight: any = localStorage.getItem("rowsHeight") || [];

      if (typeof rowsHeight === "string") {
        rowsHeight = JSON.parse(rowsHeight);
      }

      if (rowsHeight.length === 0) {
        localStorage.setItem("rowsHeight", JSON.stringify(columnRows));
      } else {
        let newCellsHeight: any = [];

        columnRows.forEach((height: any, i: any) => {
          if (typeof rowsHeight[i] !== "undefined" && height > rowsHeight[i]) {
            newCellsHeight[i] = height;
          } else if (typeof rowsHeight[i] === "undefined") {
            newCellsHeight[i] = height;
          } else {
            newCellsHeight[i] = rowsHeight[i];
          }
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
          ...imageStyle,
          height: headerHeight > 0 ? headerHeight + "px" : ""
        }}
        ref={targetRef}
      >
        {headerText}
      </div>
      {products.products.map((d: any, i: any) => {
        let selected = d.id === parseInt(hoveredRow) ? "hovered" : "";
        let heightCSS: any = [];
        let rowsHeight: any = localStorage.getItem("rowsHeight") || [];

        if (typeof rowsHeight === "string") {
          rowsHeight = JSON.parse(rowsHeight);
        }
        if (typeof rowsHeight[i] !== "undefined") {
          heightCSS = {
            height: rowsHeight[i] + "px"
          };
        }

        return (
          <div
            key={i}
            data-productid={d.id}
            className={`row ${
              i % 2 === 0 ? "odd" : "even"
            } ${selected} row${i}`}
            style={{
              ...imageStyle,
              ...heightCSS
            }}
            onMouseEnter={(e: any) => onMouseEnter({ e, setHoveredRow })}
            onMouseLeave={(e: any) => onMouseLeave({ e, setHoveredRow })}
            ref={columnRefs[i]}
          >
            {d.images.length > 0 ? (
              <Avatar
                src={d.images[0]["src"]}
                shape="square"
                style={{
                  width: imageSize.width,
                  height: imageSize.height
                }}
              />
            ) : (
              <Avatar
                src={placeholder}
                shape="square"
                style={{
                  width: imageSize.width,
                  height: imageSize.height
                }}
              />
            )}
          </div>
        );
      })}
    </>
  );
};

const mapStateToProps = (store: any) => ({
  productImageStrings: store.i18n.backend.table_elements.product_image
});

export default connect(mapStateToProps)(ProductImage);
