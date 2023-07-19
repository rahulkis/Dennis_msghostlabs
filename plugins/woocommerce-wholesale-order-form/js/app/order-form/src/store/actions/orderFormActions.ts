import { EOrderFormActionTypes } from "types/OrderFormTypes";

export const orderFormActions = {
  fetchProducts: (payload: any) => ({
    type: EOrderFormActionTypes.FETCH_PRODUCTS,
    payload
  }),
  fetchVariations: (payload: any) => ({
    type: EOrderFormActionTypes.FETCH_VARIATIONS,
    payload
  }),
  fetchCategories: (payload: any) => ({
    type: EOrderFormActionTypes.FETCH_CATEGORIES,
    payload
  }),
  setCategories: (payload: any) => ({
    type: EOrderFormActionTypes.SET_CATEGORIES,
    payload
  }),
  setAppState: (payload: any) => ({
    type: EOrderFormActionTypes.SET_APP_STATE,
    payload
  }),
  loadMoreVariations: (payload: any) => ({
    type: EOrderFormActionTypes.LOAD_MORE_VARIATIONS,
    payload
  }),
  lazyLoadProducts: (payload: any) => ({
    type: EOrderFormActionTypes.LAZY_LOAD_PRODUCTS,
    payload
  }),
  lazyLoadVariations: (payload: any) => ({
    type: EOrderFormActionTypes.LAZY_LOAD_VARIATIONS,
    payload
  }),
  setShowModal: (payload: any) => ({
    type: EOrderFormActionTypes.SET_SHOW_MODAL,
    payload
  }),
  getVariationWholesaleData: (payload: any) => ({
    type: EOrderFormActionTypes.GET_VARIATION_WHOLESALE_DATA,
    payload
  }),
  getVariationsWholesaleData: (payload: any) => ({
    type: EOrderFormActionTypes.GET_VARIATIONS_WHOLESALE_DATA,
    payload
  })
};
