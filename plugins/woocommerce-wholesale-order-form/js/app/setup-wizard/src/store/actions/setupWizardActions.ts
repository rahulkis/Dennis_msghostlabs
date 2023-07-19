import { ESetupWizardTypes } from "types/index";

export const setupWizardActions = {
  fetchSteps: (payload: any) => ({
    type: ESetupWizardTypes.FETCH_STEPS,
    payload,
  }),
  setWizardSteps: (payload: any) => ({
    type: ESetupWizardTypes.SET_WIZARD_STEPS,
    payload,
  }),
  generateAPIKeys: (payload: any) => ({
    type: ESetupWizardTypes.GENERATE_API_KEYS,
    payload,
  }),
  setAPIKeys: (payload: any) => ({
    type: ESetupWizardTypes.SET_API_KEYS,
    payload,
  }),
  migrateOldForms: (payload: any) => ({
    type: ESetupWizardTypes.MIGRATE_OLD_FORMS,
    payload,
  }),
  setGeneratedOrderForms: (payload: any) => ({
    type: ESetupWizardTypes.SET_GENERATED_ORDER_FORMS,
    payload,
  }),
  setupDone: (payload: any) => ({
    type: ESetupWizardTypes.SETUP_DONE,
    payload,
  }),
};
