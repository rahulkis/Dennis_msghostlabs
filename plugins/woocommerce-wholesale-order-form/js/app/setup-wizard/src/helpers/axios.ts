import axios from "axios";
import { ISetupWizardOptions } from "types/index";

declare var setup_wizard_options: ISetupWizardOptions;

let headers = {};

if (
  typeof setup_wizard_options !== "undefined" &&
  setup_wizard_options.nonce !== ""
) {
  headers = {
    "X-WP-Nonce": setup_wizard_options.nonce,
  };
}

export default axios.create({
  baseURL: setup_wizard_options.root,
  timeout: 0,
  headers: {
    ...headers,
  },
});
