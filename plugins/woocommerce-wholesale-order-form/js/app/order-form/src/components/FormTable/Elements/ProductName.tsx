import React, { useState, useEffect } from "react";
import { connect } from "react-redux";
import { bindActionCreators, Dispatch } from "redux";
import { orderFormActions } from "store/actions";
import { htmlDecode } from "helpers/htmlDecode";
import VariationAttributes from "./shared/VariationAttributes";
import DisplayVariationDropdown from "./shared/DisplayVariationDropdown";

const { setShowModal } = orderFormActions;

const ProductName = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
    actions
  } = props;

  const { setShowModal } = actions;

  const [productName, setProductName] = useState(product.name);

  const formProductsVariations =
    orderFormData?.formProducts?.[orderFormId]?.["variations"];

  const variations = formProductsVariations?.[product.id];

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof selectedProducts !== "undefined" &&
        Object.keys(selectedProducts).length > 0
      ) {
        const variationID = selectedProduct?.["variationID"];

        const variations =
          orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
            product.id
          ];

        if (
          typeof selectedProduct !== "undefined" &&
          product.type === "variable" &&
          typeof variations !== "undefined" &&
          Array.isArray(variations) &&
          variations.length > 0 &&
          variationID > 0
        ) {
          const variationData = variations.find((variation: any) => {
            return variation.id === variationID;
          });

          if (
            typeof variationData !== "undefined" &&
            typeof variationData.name !== "undefined"
          )
            setProductName(variationData.name);
        }
      } else {
        setProductName(product.name);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  const alignment =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "justifyContent"
    }) ?? "center";

  const alignmentCSS = {
    textAlign:
      alignment === "flex-start"
        ? ("left" as const)
        : alignment === "flex-end"
        ? ("right" as const)
        : ("center" as const)
  };

  const onClickAction =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "onClick"
    }) ?? "show-product-details";

  const variationSelector =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "variationSelectorStyle"
    }) ?? "combo";

  const displayVariationDropdown =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "displayVariationDropdown"
    }) ?? false;

  let attributes = {};

  // Check if we need to open it in new tab or redirect directly
  if (onClickAction === "navigate-to-product-page") {
    const openInNewTab =
      getPropValue({
        formStyles,
        item: itemId,
        prop: "openInNewTab"
      }) ?? true;

    attributes = {
      href: product.permalink,
      target: openInNewTab ? "_blank" : "_self"
    };
  }

  return (
    <div
      className="item product-name"
      style={{
        minWidth: "200px",
        ...alignmentCSS,
        display: "flex",
        flexDirection: "column",
        alignItems: alignment
      }}
    >
      <a
        type="link"
        {...attributes}
        className="product-name link"
        style={{ padding: "0px", fontWeight: 400, ...alignmentCSS }}
        onClick={(e: React.SyntheticEvent<HTMLAnchorElement>) => {
          if (onClickAction === "show-product-details") {
            setShowModal({
              showModal: true,
              modalProps: {
                orderFormId,
                product,
                onClickAction
              }
            });
          }
        }}
      >
        {htmlDecode(productName)}
      </a>
      <VariationAttributes product={product} />
      {displayVariationDropdown === true ? (
        <DisplayVariationDropdown
          {...props}
          variationSelector={variationSelector}
          variations={variations}
        />
      ) : (
        <></>
      )}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({ setShowModal }, dispatch)
});
export default connect(mapStateToProps, mapDispatchToProps)(ProductName);
