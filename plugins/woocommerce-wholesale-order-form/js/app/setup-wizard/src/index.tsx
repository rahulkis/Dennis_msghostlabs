import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter } from "react-router-dom";
import App from "./App";
import { ISetupWizardOptions } from "types/index";

// Store
import store from "./store";
import { Provider } from "react-redux";
import { Result } from "antd";

declare var setup_wizard_options: ISetupWizardOptions;

if (typeof setup_wizard_options.root !== "undefined") {
  ReactDOM.render(
    <Provider store={store}>
      <BrowserRouter>
        <React.StrictMode>
          <App />
        </React.StrictMode>
      </BrowserRouter>
    </Provider>,
    document.getElementById("wwof-setup-wizard")
  );
} else {
  ReactDOM.render(
    <Result
      status="warning"
      title="You have missing environment variables. Please see readme.txt for the proper setup."
    />,
    document.getElementById("wwof-setup-wizard")
  );
}
