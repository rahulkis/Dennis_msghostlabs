import { all } from "redux-saga/effects";

// Sagas
import * as setupWizard from "./setupWizardSaga";

export default function* rootSaga() {
  yield all([...setupWizard.actionListener]);
}
