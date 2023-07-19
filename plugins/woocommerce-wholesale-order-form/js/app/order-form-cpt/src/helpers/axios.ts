import axios from "axios";

declare var WWOF_Backend_Options: any;
let headers = {};

if (
  typeof WWOF_Backend_Options !== "undefined" &&
  WWOF_Backend_Options.nonce !== ""
) {
  headers = {
    "X-WP-Nonce": WWOF_Backend_Options.nonce
  };
}

export default axios.create({
  baseURL: WWOF_Backend_Options.root,
  timeout: 0,
  headers: {
    ...headers
  }
});
