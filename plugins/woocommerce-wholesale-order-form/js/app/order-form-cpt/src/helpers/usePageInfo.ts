import { useLocation } from "react-router-dom";

export default function usePageInfo(orderFormID: number | null) {
  const pathName: string = useLocation().pathname;
  const params: string = useLocation().search;
  const urlParams: URLSearchParams = new URLSearchParams(params);
  const editPath: string = `${pathName}?page=order-forms&sub-page=edit&post=${orderFormID}`;

  const pageType = urlParams.get("sub-page");
  const postID = urlParams.get("post") || 0;

  return {
    pathName,
    params,
    urlParams,
    editPath,
    pageType,
    postID,
  };
}
