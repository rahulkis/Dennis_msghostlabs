import ReactDOM from "react-dom";
import App from "./App";
import { ConfigProvider } from "antd";

// Store
import store from "./store";
import { Provider } from "react-redux";

declare var WWOF_Frontend_Options: any;

if (WWOF_Frontend_Options.root !== "%REACT_APP_ROOT%") {
  // Find all DOM containers, and render order form into them.
  document.querySelectorAll(".order-form").forEach((domContainer: any) => {
    ReactDOM.render(
      <Provider store={store}>
        <ConfigProvider
          direction={WWOF_Frontend_Options?.is_rtl ? "rtl" : "ltr"}
        >
          <App
            attributes={JSON.parse(
              domContainer.attributes["data-order-form-attr"].value
            )}
          />
        </ConfigProvider>
      </Provider>,
      domContainer
    );
  });
} else {
  document.querySelectorAll(".order-form").forEach((domContainer: any) => {
    ReactDOM.render(
      <p>
        You have missing environment variables. Please see readme.txt for the
        proper setup.
      </p>,
      domContainer
    );
  });
}
