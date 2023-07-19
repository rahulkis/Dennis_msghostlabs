export enum ESetupWizardTypes {
  FETCH_STEPS = "FETCH_STEPS",
  SET_WIZARD_STEPS = "SET_WIZARD_STEPS",
  GENERATE_API_KEYS = "GENERATE_API_KEYS",
  SET_API_KEYS = "SET_API_KEYS",
  MIGRATE_OLD_FORMS = "MIGRATE_OLD_FORMS",
  SET_GENERATED_ORDER_FORMS = "SET_GENERATED_ORDER_FORMS",
  SETUP_DONE = "SETUP_DONE"
}

export interface ISetupWizard {
  steps: any[];
  generatedOrderForms: any[];
  i18n: any[];
}

export interface ISetupWizardOptions {
  ajaxurl: string;
  root: string;
  nonce: string;
  uid: number;
  migration: string;
}
