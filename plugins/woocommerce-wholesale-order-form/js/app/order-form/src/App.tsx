import { useEffect, useState } from "react";
import FormHeaderFooter from "./components/FormHeaderFooter";
import FormTable from "./components/FormTable";
import { Tooltip, Alert, Skeleton, Space } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

// Actions
import {
  orderFormActions,
  orderFormDataActions,
  i18nActions
} from "./store/actions/";
import { getPropValue } from "./helpers/getPropValue";
import { getProductMinQtyAndStep } from "./helpers/getProductMinQtyAndStep";
import "./styles.js";
import useFormTableData from "helpers/useFormTableData";
import LoadingSkeleton from "LoadingSkeleton";

const {
  fetchProducts,
  fetchVariations,
  fetchCategories,
  getVariationWholesaleData,
  getVariationsWholesaleData
} = orderFormActions;

const {
  fetchOrderFormData,
  getMinOrderReqNotice,
  setSelectedProductsToAddToCart
} = orderFormDataActions;
const { fetchStrings } = i18nActions;

declare var WWOF_Frontend_Options: any;

const ShowMinimumRequirementNotice = (props: any) => {
  const { orderFormData, postId } = props;

  const message = orderFormData?.formMinOrderReqNotice?.[postId]?.["message"];

  if (typeof message !== "undefined" && message !== "") {
    return (
      <>
        <Alert
          style={{ marginBottom: "1rem", fontSize: "18px" }}
          message={
            <div
              style={{ paddingLeft: "10px" }}
              dangerouslySetInnerHTML={{
                __html: message
              }}
            />
          }
          type="info"
          showIcon
        />
      </>
    );
  } else return <></>;
};

const OrderFormTitle = (props: any) => {
  const { orderFormData, attributes, postId, frontendStrings } = props;
  const formTitle = orderFormData?.formTitle?.[postId];
  const hideFormTitle =
    orderFormData?.formSettings?.[postId]?.hide_form_title ?? false;

  if (typeof formTitle === "undefined" || hideFormTitle === true) return <></>;

  // Show tooltip for draft order form.
  if (
    typeof attributes.post_status !== "undefined" &&
    attributes.post_status === "draft"
  )
    return (
      <h2>
        {formTitle}
        <Tooltip title={frontendStrings?.app?.draft_tooltip}>
          <InfoCircleOutlined
            style={{ fontSize: "18px", marginLeft: "10px", color: "red" }}
          />
        </Tooltip>
      </h2>
    );
  else return <h2>{formTitle}</h2>;
};

const App = (props: any) => {
  const {
    orderForm,
    orderFormData,
    attributes,
    formStyles,
    formProducts,
    frontendStrings
  } = props;
  const postId = parseInt(attributes.id);
  // Form Products Data
  const { fetching, fetchingVariations } = useFormTableData({
    orderFormData,
    orderFormId: postId
  });

  const [test, setTest] = useState(false);

  const {
    fetchProducts,
    fetchVariations,
    fetchCategories,
    fetchOrderFormData,
    getMinOrderReqNotice,
    setSelectedProductsToAddToCart,
    fetchStrings,
    getVariationWholesaleData,
    getVariationsWholesaleData
  } = props.actions;

  useEffect(() => {
    fetchStrings();
  }, []);

  // Pre-populate formSelectedProducts with current variable products with pre-selected variation
  useEffect(() => {
    try {
      // Exit if pre select variation is disabled via wp filter
      if (!WWOF_Frontend_Options?.pre_selected_variations) {
        return;
      }

      // Return if no data yet or still fetching

      if (fetching !== false || fetchingVariations !== false) {
        return;
      }

      if (formProducts?.products && formProducts?.fetching === false) {
        let variableProducts: any = {};

        formProducts.products.map((product: any, i: number) => {
          if (
            product.type === "variable" &&
            typeof formProducts.variations[product.id] !== "undefined" &&
            Object.entries(formProducts.variations[product.id]).length > 0
          ) {
            let variationID = formProducts.variations[product.id][0]?.id; // Get first variation
            let defaultVariationID = 0;
            let variationList = formProducts.variations[product.id];
            let attributeData = {
              total: product.attributes.length,
              defaultTotal: product.default_attributes.length
            };
            let variationData: any = [];
            let variationName: string = "";
            let stockStatus: string = "";
            let selectedAtts: string[] = [];

            // Get default attribute
            // This is set in product editor
            if (
              attributeData.total === attributeData.defaultTotal &&
              variationList.length > 0
            ) {
              for (const [, variation] of variationList.entries()) {
                const attributes = variation.attributes;
                let match = 0;
                for (const [j, attr] of attributes.entries()) {
                  if (
                    product.default_attributes[j].id === attr.id &&
                    product.default_attributes[j].name.toLowerCase() ===
                      attr.name.toLowerCase() &&
                    product.default_attributes[j].option.toLowerCase() ===
                      attr.option.toLowerCase()
                  ) {
                    match += 1;
                  }
                }
                if (match === attributeData.total) {
                  variationID = variation.id;
                  defaultVariationID = variation.id;
                }
              }
            }

            // Product Stock Status
            stockStatus = product.stock_status;

            if (variationID !== 0 && typeof variationID !== "undefined") {
              variationData = formProducts.variations[product.id].find(
                (data: any) => {
                  return data.id === variationID;
                }
              );

              // Variation Name
              if (
                variationData !== undefined &&
                typeof variationData?.attributes !== "undefined"
              ) {
                variationName = variationData.attributes.map(
                  (attributes: any) => {
                    return attributes.name + ": " + attributes.option;
                  }
                );

                variationData.attributes.map((data: any, i: number) => {
                  selectedAtts = {
                    ...selectedAtts,
                    [data.name]: data.option.toLowerCase()
                  };
                  return true;
                });
              }

              // Variation Stock Status
              stockStatus = variationData.stock_status;

              // Variation Wholesale Data
              variationData =
                typeof variationData.wholesale_data !== "undefined"
                  ? variationData.wholesale_data
                  : [];
            }

            // Min Quantity and Step
            const { minOrderQty } = getProductMinQtyAndStep({
              productType: product.type,
              wholesaleData:
                typeof product.wholesale_data !== "undefined"
                  ? product.wholesale_data
                  : [],
              variationData,
              variationID
            });

            // Maintain the values when navigating to different page. Specially the selected value.
            if (
              typeof orderFormData.formSelectedProducts[postId] !==
                "undefined" &&
              typeof orderFormData.formSelectedProducts[postId][product.id] !==
                "undefined"
            ) {
              variableProducts[product.id] = {
                ...orderFormData.formSelectedProducts[postId][product.id],
                productTitle: product.name,
                productID: product.id,
                variationID,
                productType: product.type,
                name: variationName,
                defaultVariationID,
                quantity: minOrderQty,
                stockStatus,
                variationData,
                wholesaleData:
                  typeof product.wholesale_data !== "undefined"
                    ? product.wholesale_data
                    : [],
                selectedAtts
              };
            } else {
              variableProducts[product.id] = {
                productTitle: product.name,
                productID: product.id,
                variationID,
                productType: product.type,
                name: variationName,
                defaultVariationID,
                quantity: minOrderQty,
                stockStatus,
                variationData,
                wholesaleData:
                  typeof product.wholesale_data !== "undefined"
                    ? product.wholesale_data
                    : [],
                selectedAtts
              };
            }
          }

          return true;
        });

        setSelectedProductsToAddToCart({
          ...orderFormData.formSelectedProducts,
          [postId]: {
            ...orderFormData.formSelectedProducts[postId],
            ...variableProducts
          }
        });
      }
    } catch (e) {
      console.log(e);
    }
  }, [formProducts, fetching, fetchingVariations]);

  // Fetch Order Form Data
  useEffect(() => {
    try {
      // Fetch order form data
      if (attributes.id !== undefined && postId > 0) {
        fetchOrderFormData({
          id: postId,
          successCB: (response: any) => {},
          failCB: () => {
            console.log("Data cannot be fetched!");
          }
        });
      }
    } catch (e) {
      console.log(e);
    }
  }, [attributes.id, fetchOrderFormData, postId]);

  // Fetch products
  // Fired after order form data is fetched
  useEffect(() => {
    try {
      if (typeof orderFormData.formSettings[postId] !== "undefined") {
        fetchProducts({
          orderFormData,
          sort_order: orderFormData.formSettings[postId].sort_order ?? "desc",
          sort_by: orderFormData.formSettings[postId].sort_by ?? "date",
          search: "",
          category: orderFormData.formSettings[postId].selected_category ?? "",
          active_page: 1,
          searching: "no",
          products: attributes.products ?? "",
          categories:
            orderFormData.formSettings[postId].filtered_categories ?? "",
          show_all: false,
          attributes,
          wholesale_role: WWOF_Frontend_Options.wholesale_role,
          per_page:
            orderFormData.formSettings[postId]["products_per_page"] ?? 10,
          form_settings: orderFormData.formSettings[postId],
          uid: WWOF_Frontend_Options.uid,
          successCB: (response: any) => {
            // Only trigger if only show variations individually is NOT enabled
            if (
              !orderFormData?.formSettings?.[postId]
                ?.show_variations_individually &&
              response?.formProducts?.products.length > 0
            ) {
              // Get the variations
              fetchVariations({
                wholesale_role: WWOF_Frontend_Options.wholesale_role,
                orderFormId: response?.orderFormId,
                formProducts: response?.formProducts,
                form_settings: orderFormData.formSettings[postId],
                successCB: (response: any) => {
                  // Get the variations wholesale data
                  getVariationsWholesaleData({
                    wholesale_role: WWOF_Frontend_Options.wholesale_role,
                    orderFormId: postId,
                    selectedProducts:
                      orderFormData.formSelectedProducts[postId],
                    formProducts: response?.formProducts?.products,
                    variations: response?.data?.variations,
                    successCB: (data: any) => {},
                    failCB: () => {
                      console.log("error");
                    }
                  });
                },
                failCB: (response: any) =>
                  console.log(`fetchVariations: ${response?.data?.message}`)
              });
            }
          },
          failCB: (response: any) =>
            console.log(`fetchProducts: ${response?.data?.message}`)
        });
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSettings[postId]]);

  // Fetch categories
  useEffect(() => {
    try {
      fetchCategories({
        categories: orderForm.categories,
        successCB: (response: any) => {},
        failCB: (response: any) =>
          console.log(`fetchCategories: ${response?.data?.message}`)
      });
    } catch (e) {
      console.log(e);
    }
  }, []);

  // Fetch min requirement notice
  useEffect(() => {
    if (typeof orderFormData.formSettings[postId] !== "undefined") {
      let showMinReqNotice = getPropValue({
        formStyles,
        item: "formHeader",
        prop: "showMinReqNotice"
      });

      if (showMinReqNotice === null || showMinReqNotice === 1)
        showMinReqNotice = true;

      if (
        showMinReqNotice === true &&
        typeof WWOF_Frontend_Options !== "undefined" &&
        WWOF_Frontend_Options.wwpp_active !== "" &&
        attributes.id !== undefined &&
        postId > 0 &&
        WWOF_Frontend_Options.wholesale_role !== ""
      ) {
        getMinOrderReqNotice({
          postId,
          wholesale_role: WWOF_Frontend_Options.wholesale_role,
          uid: WWOF_Frontend_Options.uid,
          successCB: (response: any) => {},
          failCB: () => {
            console.log("Data cannot be fetched!");
          }
        });
      }
    }
  }, [orderFormData.formSettings[postId]]);

  // Make sure translatable strings are loaded
  if (frontendStrings?.length <= 0) {
    return <LoadingSkeleton />;
  }

  if (attributes.id !== undefined && postId > 0) {
    return (
      <>
        <ShowMinimumRequirementNotice {...props} postId={postId} />
        <OrderFormTitle {...props} postId={postId} />
        {Object.keys(orderFormData.formHeader).length > 0 ? (
          <FormHeaderFooter section="formHeader" orderFormId={postId} />
        ) : (
          ""
        )}
        {Object.keys(orderFormData.formTable).length > 0 ? (
          <FormTable orderFormId={postId} />
        ) : (
          ""
        )}
        {Object.keys(orderFormData.formFooter).length > 0 ? (
          <FormHeaderFooter section="formFooter" orderFormId={postId} />
        ) : (
          ""
        )}
      </>
    );
  } else return <></>;
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  attributes: props.attributes,
  formStyles: store.orderFormData.formStyles[props.attributes.id],
  formProducts: store.orderFormData.formProducts[props.attributes.id],
  frontendStrings: store.i18n.frontend
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      fetchProducts,
      fetchVariations,
      fetchCategories,
      fetchOrderFormData,
      getMinOrderReqNotice,
      setSelectedProductsToAddToCart,
      fetchStrings,
      getVariationWholesaleData,
      getVariationsWholesaleData
    },
    dispatch
  )
});

export default connect(mapStateToProps, mapDispatchToProps)(App);
