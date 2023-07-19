import ReactDOM from "react-dom";
import App from "./App";
import { BrowserRouter } from "react-router-dom";
import { ConfigProvider } from "antd";

// Store
import store from "./store";
import { Provider } from "react-redux";

import "./styles.scss";

declare var WWOF_Backend_Options: any;

if (WWOF_Backend_Options.root !== "%REACT_APP_ROOT%") {
  ReactDOM.render(
    <Provider store={store}>
      <BrowserRouter>
        <ConfigProvider
          direction={WWOF_Backend_Options?.is_rtl ? "rtl" : "ltr"}
        >
          <App />
        </ConfigProvider>
      </BrowserRouter>
    </Provider>,
    document.getElementById("wwof-order-forms-admin")
  );
} else {
  ReactDOM.render(
    <p>
      You have missing environment variables. Please see readme.txt for the
      proper setup.
    </p>,
    document.getElementById("wwof-order-forms-admin")
  );
}
