import { combineReducers } from "redux";

// Reducers
import setupWizardReducer from "./setupWizardReducer";

const reducers = combineReducers({
  setupWizard: setupWizardReducer,
});

export default reducers;
