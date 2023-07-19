import { takeEvery, put, call } from "redux-saga/effects";
import { setupWizardActions } from "store/actions/index";
import { ESetupWizardTypes, IResponseGenerator } from "types/index";
import axiosInstance from "helpers/axios";
import { ISetupWizardOptions } from "types/index";

declare var setup_wizard_options: ISetupWizardOptions;

export function* fetchSteps(action: any) {
  try {
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/setup-wizard/`, {
        params: {
          migration: setup_wizard_options.migration
        }
      })
    );

    if (response && response.data) {
      yield put(
        setupWizardActions.setWizardSteps({
          steps: response.data.steps,
          i18n: response.data.i18n
        })
      );
    }
  } catch (e) {
    console.log(e);
  }
}

export function* generateAPIKeys(action: any) {
  try {
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(`wwof/v1/api-keys/`, {
        uid: setup_wizard_options.uid
      })
    );

    if (response && response.data && response.data?.success === true) {
      yield put(setupWizardActions.setAPIKeys(response.data));
    }
  } catch (e) {
    console.log(e);
  }
}

export function* migrateOldForms(action: any) {
  try {
    const { successCB, failCB } = action.payload;
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(`wwof/v1/setup-wizard/`, {
        migration: setup_wizard_options.migration
      })
    );

    if (response && response.data && response.data?.success === true) {
      yield put(setupWizardActions.setGeneratedOrderForms(response.data));

      if (typeof successCB === "function") successCB(response);
      else if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export function* setupDone(action: any) {
  try {
    const { successCB, failCB } = action.payload;
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(`wwof/v1/setup-wizard/setup-done/`)
    );

    if (response && response.data && response.data?.success === true) {
      if (typeof successCB === "function") successCB(response);
      else if (typeof failCB === "function") failCB(response);
    }
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(ESetupWizardTypes.FETCH_STEPS, fetchSteps),
  takeEvery(ESetupWizardTypes.GENERATE_API_KEYS, generateAPIKeys),
  takeEvery(ESetupWizardTypes.MIGRATE_OLD_FORMS, migrateOldForms),
  takeEvery(ESetupWizardTypes.SETUP_DONE, setupDone)
];
