import {
  EOrderFormDataActionTypes,
  IOrderFormData
} from "types/OrderFormDataTypes";
import { EOrderFormActionTypes } from "types/OrderFormTypes";

const defaults = {
  formTitle: [],
  formFooter: [],
  formHeader: [],
  formTable: [],
  formStyles: [],
  formSettings: [],
  formProducts: [],
  formPagination: [],
  formFilters: [],
  formSelectedProducts: [],
  formCartSubtotal: [],
  formTableSorter: [],
  formMinOrderReqNotice: []
};

export default function orderFormDataReducer(
  state: IOrderFormData = defaults,
  action: any
) {
  switch (action.type) {
    case EOrderFormDataActionTypes.SET_ORDER_FORM_DATA:
      const {
        formTitle,
        formFooter,
        formHeader,
        formTable,
        formStyles,
        formSettings
      } = action.payload;

      return {
        ...state,
        formTitle: {
          ...state.formTitle,
          ...formTitle
        },
        formFooter: {
          ...state.formFooter,
          ...formFooter
        },
        formHeader: {
          ...state.formHeader,
          ...formHeader
        },
        formTable: {
          ...state.formTable,
          ...formTable
        },
        formStyles: {
          ...state.formStyles,
          ...formStyles
        },
        formSettings: {
          ...state.formSettings,
          ...formSettings
        }
      };

    case EOrderFormDataActionTypes.SET_ORDER_FORM_PRODUCTS:
      return {
        ...state,
        formProducts: {
          ...state.formProducts,
          ...action.payload
        }
      };

    case EOrderFormDataActionTypes.SET_ORDER_FORM_PAGINATION:
      return {
        ...state,
        formPagination: {
          ...state.formPagination,
          [action.payload.orderFormId]: {
            ...state.formPagination[action.payload.orderFormId],
            ...action.payload.data
          }
        }
      };

    case EOrderFormDataActionTypes.SET_PAGE_SELECTED_ALL:
      const { orderFormId, data } = action.payload;
      return {
        ...state,
        formPagination: {
          ...state.formPagination,
          [orderFormId]: {
            ...state.formPagination[orderFormId],
            selectedAll: {
              ...state.formPagination[orderFormId].selectedAll,
              ...data
            }
          }
        }
      };

    case EOrderFormDataActionTypes.SET_ORDER_FORM_FILTERS:
      return {
        ...state,
        formFilters: {
          ...state.formFilters,
          ...action.payload
        }
      };

    case EOrderFormDataActionTypes.SET_ORDER_FORM_SELECTED_PRODUCTS_TO_ADD_CART:
      return {
        ...state,
        formSelectedProducts: {
          ...state.formSelectedProducts,
          ...action.payload
        }
      };

    case EOrderFormDataActionTypes.SET_ORDER_FORM_CART_SUBTOTAL:
      return {
        ...state,
        formCartSubtotal: {
          ...state.formCartSubtotal,
          ...action.payload
        }
      };

    case EOrderFormDataActionTypes.SET_ORDER_FORM_TABLE_SORTER:
      return {
        ...state,
        formTableSorter: {
          ...action.payload
        }
      };

    case EOrderFormDataActionTypes.SET_MIN_ORDER_REQ_NOTICE:
      return {
        ...state,
        formMinOrderReqNotice: {
          ...state.formMinOrderReqNotice,
          ...action.payload
        }
      };

    case EOrderFormActionTypes.SET_VARIATION_WHOLESALE_DATA:
      const { formId, productID, variationID, wholesale_data } = action.payload;

      let updatedVariations = state?.formProducts?.[formId]?.variations?.[
        productID
      ].map((data: any) => {
        if (parseInt(data?.id) === variationID) {
          return {
            ...data,
            wholesale_data
          };
        }
        return data;
      });

      return {
        ...state,
        formProducts: {
          ...state.formProducts,
          [formId]: {
            ...state.formProducts[formId],
            fetching: undefined,
            fetching_variations_wholesale_data: true,
            variations: {
              ...state.formProducts[formId].variations,
              [productID]: [...updatedVariations]
            }
          }
        }
      };

    case EOrderFormActionTypes.SET_VARIATIONS_WHOLESALE_DATA:
      let formId2 = action.payload.orderFormId;
      let data2: any = action.payload.data;
      let updatedVariations2 = state?.formProducts?.[formId2]?.variations;

      Object.keys(updatedVariations2).map((d: any) => {
        if (data2[d]) {
          updatedVariations2[d].map((i: any, k: any) => {
            if (parseInt(data2[d].variationId) === parseInt(i?.id)) {
              updatedVariations2[d][k] = {
                ...i,
                wholesale_data: data2[d]?.wholesale_data
              };
            }
          });
        }
      });

      return {
        ...state,
        formProducts: {
          ...state.formProducts,
          [formId2]: {
            ...state.formProducts[formId2],
            variations: updatedVariations2
          }
        }
      };
    default:
      return state;
  }
}
