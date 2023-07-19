import { takeEvery, put, call } from "redux-saga/effects";
import { EProductActionTypes, IResponseGenerator } from "types/index";
import axiosInstance from "helpers/axios";

import {
  productActions,
  orderFormActions,
  paginationActions
} from "store/actions/index";

declare var WWOF_Backend_Options: any;

export function* fetchProducts(action: any) {
  const { orderFormId, successCB, failCB } = action.payload;

  try {
    yield put(productActions.setFetchingProducts(true));

    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Backend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_products",
          nonce: WWOF_Backend_Options.nonce,
          ...action.payload
        })
      )
    );

    if (response && response.data && response.data.status === "success") {
      yield put(productActions.setFetchingProducts(false));
      yield put(orderFormActions.setCartSubtotal(response.data.cart_subtotal));
      yield put(productActions.setProducts(response.data.products));
      yield put(
        paginationActions.setPaginationState({
          active_page: 1,
          per_page: 10,
          total_products: parseInt(response.data.total_products)
        })
      );

      let variable = response?.data?.products.filter(
        (product: any) => product?.type === "variable"
      );
      let variableProducts: any = {};

      variable.forEach((product: any) => {
        variableProducts[product.id] = {
          fetching: true,
          id: product?.id,
          type: product?.type
        };
      });

      // Lazy loading data
      yield put(
        productActions.setLazyLoadData(response.data.lazy_load_variations_data)
      );

      if (typeof successCB === "function") {
        successCB({ ...response, orderFormId, products: variableProducts });
      }
    } else if (typeof failCB === "function") failCB(response);
  } catch (e) {
    console.log(e);
  }
}

export function* fetchVariations(action: any) {
  const { wholesale_role, products, successCB, failCB } = action.payload;

  try {
    yield put(productActions.setFetchingVariations(true));

    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Backend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_variations",
          wholesale_role,
          products
        })
      )
    );

    if (response && response.data && response.data.status === "success") {
      yield put(productActions.setFetchingVariations(false));
      yield put(productActions.setVariations(response?.data?.variations));

      if (typeof successCB === "function") {
        successCB();
      }
    } else if (typeof failCB === "function") failCB();
  } catch (e) {
    console.log(e);
  }
}

export function* fetchCategories(action: any) {
  const { categories, successCB, failCB } = action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Backend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_categories",
          nonce: WWOF_Backend_Options.nonce,
          categories: categories
        })
      )
    );

    if (response && response.data && response.data.status === "success") {
      yield put(productActions.setCategories(response.data.categories));
      if (typeof successCB === "function") {
        successCB(response);
      }
    } else if (typeof failCB === "function") failCB(response);
  } catch (e) {
    console.log(e);
  }
}

// Load more variations - lazy loading
export function* loadMoreVariations(action: any) {
  const { orderForm, products, product_id, current_page, successCB, failCB } =
    action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Backend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_variations",
          product_id,
          current_page,
          form_settings: {
            ...orderForm.settingsData
          }
        })
      )
    );

    if (response && response.data.status === "success") {
      // Add more variations
      yield put(
        productActions.setVariations({
          ...products.variations,
          [product_id]: [
            ...products.variations[product_id],
            ...response.data.variations
          ]
        })
      );

      // Lazy loading data
      yield put(
        productActions.setLazyLoadData({
          ...products.lazy_load_variations_data,
          [product_id]: {
            ...products.lazy_load_variations_data[product_id],
            current_page
          }
        })
      );

      if (typeof successCB === "function") {
        successCB(response);
      }
    } else if (typeof successCB === "function") failCB();
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(EProductActionTypes.FETCH_PRODUCTS, fetchProducts),
  takeEvery(EProductActionTypes.FETCH_VARIATIONS, fetchVariations),
  takeEvery(EProductActionTypes.FETCH_CATEGORIES, fetchCategories),
  takeEvery(EProductActionTypes.LOAD_MORE_VARIATIONS, loadMoreVariations)
];
