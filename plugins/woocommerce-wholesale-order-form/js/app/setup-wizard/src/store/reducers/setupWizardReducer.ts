import { ESetupWizardTypes, ISetupWizard } from "types/index";

export const defaults = {
  steps: [],
  generatedOrderForms: [],
  i18n: []
};

export default function orderFormReducer(
  state: ISetupWizard = defaults,
  action: any
) {
  switch (action.type) {
    case ESetupWizardTypes.SET_WIZARD_STEPS:
      const { steps, i18n } = action.payload;
      return {
        ...state,
        steps,
        i18n
      };

    case ESetupWizardTypes.SET_API_KEYS:
      const { consumer_key, consumer_secret } = action.payload.data;

      const stepsCopy = [...state.steps];
      const step2 = stepsCopy.find((x: any) => x.id === "step2");
      if (typeof step2 !== "undefined") {
        step2["data"] = { is_valid: true, consumer_key, consumer_secret };
      }

      return {
        ...state,
        steps: state.steps.map((step: any) => {
          if (step.id === "step2") {
            step.data = { is_valid: true, consumer_key, consumer_secret };
          }
          return step;
        })
      };

    case ESetupWizardTypes.SET_GENERATED_ORDER_FORMS:
      return {
        ...state,
        generatedOrderForms: action.payload.data
      };

    default:
      return state;
  }
}
