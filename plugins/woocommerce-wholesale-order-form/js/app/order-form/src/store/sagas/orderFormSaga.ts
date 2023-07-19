import { takeEvery, put, call } from "redux-saga/effects";
import { EOrderFormActionTypes, IResponseGenerator } from "types/index";
import { orderFormActions, orderFormDataActions } from "store/actions/index";
import axiosInstance from "helpers/axios";

declare var WWOF_Frontend_Options: any;

// The initial fetching of products
// Condition:
// - When loading the order form page
export function* fetchProducts(action: any) {
  const {
    orderFormData,
    search,
    category,
    active_page,
    searching,
    sort_order,
    show_all,
    products,
    categories,
    attributes,
    wholesale_role,
    per_page,
    sort_by,
    allow_sku_search,
    form_settings,
    uid,
    successCB,
    failCB
  } = action.payload;

  try {
    if (attributes.id !== undefined) {
      yield put(
        orderFormDataActions.setOrderFormProducts({
          [attributes.id]: {
            ...orderFormData.formProducts[attributes.id],
            fetching: true,
            fetchingVariations: undefined,
            fetching_variations_wholesale_data: false
          }
        })
      );
    }

    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_products",
          nonce: WWOF_Frontend_Options.nonce,
          search,
          category,
          page: active_page || 1,
          searching: searching || "no",
          sort_order: sort_order || [],
          sort_by: sort_by || "",
          products: products || [],
          categories,
          show_all,
          wholesale_role,
          per_page,
          allow_sku_search: allow_sku_search || "",
          form_settings,
          uid,
          // WC Aelia Currency Switcher integration
          aelia_selected_currency: (window as any).wc_aelia_currency_switcher_params?.selected_currency ?? ""
        })
      )
    );

    let productsData = {};

    if (response && response.data && response.data.status === "success") {
      const data = { ...response.data, attributes, active_page, sort_order };

      if (attributes.id !== undefined && attributes.id !== 0) {
        yield put(orderFormActions.setAppState({ data }));

        yield put(
          orderFormDataActions.setCartSubtotal({
            [attributes.id]: {
              cartSubtotal: data.cart_subtotal
            }
          })
        );

        let variable = data?.products.filter(
          (product: any) => product.type === "variable"
        );
        let variableProducts: any = {};

        variable.forEach((product: any) => {
          variableProducts[product.id] = {
            fetching: true,
            id: product?.id,
            type: product?.type
          };
        });

        productsData = {
          fetching: false,
          fetchingVariations: variableProducts.length === 0 ? false : undefined,
          products: data.products,
          variations: variableProducts,
          lazy_load_variations_data: data.lazy_load_variations_data
        };

        yield put(
          orderFormDataActions.setOrderFormProducts({
            [attributes.id]: productsData
          })
        );

        yield put(
          orderFormDataActions.setOrderFormPagination({
            orderFormId: attributes.id,
            data: {
              active_page,
              per_page,
              total_products: parseInt(data?.total_products) ?? 0,
              total_page: parseInt(data?.total_page ?? 0)
            }
          })
        );
      }

      if (typeof successCB === "function") {
        successCB({
          ...response,
          orderFormId: attributes.id,
          orderFormData,
          formProducts: productsData,
          lazyLoading: false
        });
      }
    } else if (typeof failCB === "function") failCB(response);
  } catch (e) {
    console.log(e);
  }
}

// This is triggered after fetchProducts
// Condition:
// - After fetching products, fetch variable variations
export function* fetchVariations(action: any) {
  const {
    wholesale_role,
    orderFormId,
    formProducts,
    form_settings,
    successCB,
    failCB
  } = action.payload;

  try {
    yield put(
      orderFormDataActions.setOrderFormProducts({
        [orderFormId]: {
          ...formProducts,
          fetchingVariations: true
        }
      })
    );

    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/variations`, {
        params: {
          wholesale_role,
          products: formProducts?.variations,
          form_settings,
          type: "get_variations"
        }
      })
    );

    if (response && response.data && response.data.status === "success") {
      yield put(
        orderFormDataActions.setOrderFormProducts({
          [orderFormId]: {
            ...formProducts,
            fetchingVariations: false,
            variations: response.data?.variations || [],
            lazy_load_variations_data: formProducts?.lazy_load_variations_data
          }
        })
      );

      if (typeof successCB === "function") {
        successCB({ ...response, formProducts });
      }
    } else if (typeof failCB === "function") failCB(response);
  } catch (e) {
    console.log(e);
  }
}

// Get categories
// Condition:
// - When loading the order form page
export function* fetchCategories(action: any) {
  const { categories, successCB, failCB } = action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_categories",
          categories: categories,
          nonce: WWOF_Frontend_Options.nonce,
        })
      )
    );

    if (response && response.data && response.data.status === "success") {
      const data = { ...response.data };

      yield put(
        orderFormActions.setCategories({ categories: data.categories })
      );
      if (typeof successCB === "function") {
        successCB(response);
      }
    } else if (typeof failCB === "function") {
      failCB(response);
    }
  } catch (e) {
    console.log(e);
  }
}

// Load more variations - lazy loading
// Note: Since 2.0.4 we are now fetching all variations to avoid inaccurate loaded variations since api has limitations
//        Atm, this is not being used.
// Condition:
// - When you scroll down the variation dropdown
// - Used in combo dropdown
export function* loadMoreVariations(action: any) {
  const {
    orderFormId,
    wholesale_role,
    product_id,
    current_page,
    successCB,
    failCB,
    orderFormData
  } = action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_wholesale_variations",
          product_id,
          current_page,
          wholesale_role,
          uid: WWOF_Frontend_Options.uid,
          form_settings: orderFormData.formSettings[orderFormId]
        })
      )
    );

    if (response && response.data.status === "success") {
      // Append more variations
      yield put(
        orderFormDataActions.setOrderFormProducts({
          [orderFormId]: {
            ...orderFormData.formProducts[orderFormId],
            variations: {
              ...orderFormData.formProducts[orderFormId].variations,
              [product_id]: [
                ...orderFormData.formProducts[orderFormId].variations[
                  product_id
                ],
                ...response.data.variations
              ]
            },
            lazy_load_variations_data: {
              ...orderFormData.formProducts[orderFormId]
                .lazy_load_variations_data,
              [product_id]: {
                ...orderFormData.formProducts[orderFormId]
                  .lazy_load_variations_data[product_id],
                current_page
              }
            }
          }
        })
      );

      if (typeof successCB === "function") {
        successCB(response);
      }
    } else if (typeof failCB === "function") failCB();
  } catch (e) {
    console.log(e);
  }
}

// Load more products - lazy loading
// Condition:
// - Lazy load is enabled
// - Used to fetch succeeding products(page 2 and up)
export function* lazyLoadProducts(action: any) {
  const {
    orderFormId,
    formProducts,
    formVariations,
    formLazyLoadVariationsData,
    active_page,
    per_page,
    wholesale_role,
    search,
    category,
    searching,
    sort_order,
    sort_by,
    categories,
    products,
    allow_sku_search,
    form_settings,
    uid,
    successCB,
    failCB
  } = action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_products",
          nonce: WWOF_Frontend_Options.nonce,
          search,
          category,
          page: active_page || 1,
          searching: searching || "no",
          sort_order: sort_order || [],
          sort_by: sort_by || "",
          products: products || [],
          categories,
          wholesale_role,
          per_page,
          allow_sku_search,
          form_settings,
          uid,
          // WC Aelia Currency Switcher integration
          aelia_selected_currency: (window as any).wc_aelia_currency_switcher_params?.selected_currency ?? ""
        })
      )
    );

    if (response && response.data && response.data.status === "success") {
      let variable = response.data?.products.filter(
        (product: any) => product.type === "variable"
      );
      let variableProducts: any = {};

      variable.forEach((product: any) => {
        variableProducts[product.id] = [
          {
            fetching: true,
            id: product?.id,
            type: product?.type
          }
        ];
      });

      let productsData: any = {
        fetching: false,
        fetching_variations_wholesale_data:
          variable.length === 0 ? undefined : false,
        products: [...formProducts, ...response.data.products],
        variations: {
          ...formVariations,
          ...variableProducts
        },
        lazy_load_variations_data: {
          ...formLazyLoadVariationsData,
          ...response.data.lazy_load_variations_data
        }
      };

      // Add more products to the state
      yield put(
        orderFormDataActions.setOrderFormProducts({
          [orderFormId]: productsData
        })
      );

      // Update pagination active page
      yield put(
        orderFormDataActions.setOrderFormPagination({
          orderFormId,
          data: {
            active_page
          }
        })
      );
      if (typeof successCB === "function") {
        successCB({
          ...response,
          orderFormId,
          productsData,
          formProducts: variableProducts,
          lazyLoading: true
        });
      }
    } else if (typeof failCB === "function") failCB();
  } catch (e) {
    console.log(e);
  }
}

// Fetch variation - Lazy loading
// Condition:
// - Lazy load is enabled
// - Combo or standard
// - This is only triggered when loading second and succeeding pages.
// - Triggered after lazyLoadProducts
export function* lazyLoadVariations(action: any) {
  const {
    wholesale_role,
    orderFormId,
    formProducts,
    form_settings,
    productsData,
    successCB,
    failCB
  } = action.payload;

  try {
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/variations`, {
        params: {
          wholesale_role,
          products: formProducts,
          form_settings,
          type: "get_variations"
        }
      })
    );

    if (response && response.data && response.data.status === "success") {
      yield put(
        orderFormDataActions.setOrderFormProducts({
          [orderFormId]: {
            ...productsData,
            variations: {
              ...productsData?.variations,
              ...response?.data?.variations
            }
          }
        })
      );

      if (typeof successCB === "function") {
        successCB({ ...response, formProducts: productsData?.products });
      }
    } else if (typeof failCB === "function") failCB();
  } catch (e) {
    console.log(e);
  }
}

// Condition:
// - Selecting or pres-selecting a variation
// - User is a wholesale user
//
// Triggered when auto selecting or selecting a variation
// Fetch the wholesale data. Only triggered when the user is wholesale customer.
export function* getVariationWholesaleData(action: any) {
  try {
    const {
      wholesale_role,
      orderFormId,
      productID,
      variationID,
      successCB,
      failCB
    } = action.payload;

    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/variations/`, {
        params: {
          wholesale_role,
          type: "get_variation_wholesale_data",
          variationID
        }
      })
    );

    if (response && response.data) {
      yield put(
        orderFormDataActions.setVariationWholesaleData({
          formId: orderFormId,
          productID,
          variationID,
          wholesale_data: response?.data?.wholesale_data
        })
      );

      if (typeof successCB === "function") successCB(response.data);
    } else {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

// Condition:
// - Triggered after fetching product variations
// - Get the variation data of initialy selected variation
//
// Triggered when auto selecting or selecting a variation
// Fetch the wholesale data. Only triggered when the user is wholesale customer.
export function* getVariationsWholesaleData(action: any) {
  try {
    const {
      wholesale_role,
      orderFormId,
      variations,
      formProducts,
      successCB,
      failCB
    } = action.payload;

    let defaultSelectedVariations: any[] = [];

    Object.keys(variations).forEach((key: string) => {
      let variationId = variations[key][0]?.id;
      let product = formProducts.filter(
        (product: any) => product?.id === parseInt(key)
      )[0];
      let variationList = variations[key];

      let attributeData = {
        total: product.attributes.length,
        defaultTotal: product.default_attributes.length
      };

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
            variationId = variation.id;
          }
        }
      }
      defaultSelectedVariations.push([{ productId: product.id, variationId }]);
    });

    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/variations/`, {
        params: {
          wholesale_role,
          type: "get_variations_wholesale_data",
          variations: defaultSelectedVariations
        }
      })
    );

    if (response && response.data) {
      yield put(
        orderFormDataActions.setVariationsWholesaleData({
          orderFormId,
          data: response?.data?.data
        })
      );

      if (typeof successCB === "function") successCB(response.data);
    } else {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(EOrderFormActionTypes.FETCH_PRODUCTS, fetchProducts),
  takeEvery(EOrderFormActionTypes.FETCH_VARIATIONS, fetchVariations),
  takeEvery(EOrderFormActionTypes.FETCH_CATEGORIES, fetchCategories),
  takeEvery(EOrderFormActionTypes.LOAD_MORE_VARIATIONS, loadMoreVariations),
  takeEvery(EOrderFormActionTypes.LAZY_LOAD_PRODUCTS, lazyLoadProducts),
  takeEvery(EOrderFormActionTypes.LAZY_LOAD_VARIATIONS, lazyLoadVariations),
  takeEvery(
    EOrderFormActionTypes.GET_VARIATION_WHOLESALE_DATA,
    getVariationWholesaleData
  ),
  takeEvery(
    EOrderFormActionTypes.GET_VARIATIONS_WHOLESALE_DATA,
    getVariationsWholesaleData
  )
];
