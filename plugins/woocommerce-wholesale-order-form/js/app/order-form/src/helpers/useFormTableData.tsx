import { useEffect, useState } from "react";

export default function useFormTableData(props: any) {
  const { orderFormData, orderFormId } = props;

  const [fetching, setFetching] = useState(false);
  const [fetchingVariations, setFetchingVariations] = useState(undefined);
  const [formProducts, setFormProducts] = useState([]);
  const [formVariations, setFormVariations] = useState([]);
  const [formLazyLoadVariationsData, setFormLazyLoadVariationsData] = useState(
    []
  );
  const [formStyles, setFormStyles] = useState([]);
  const [formTable, setFormTable] = useState([]);
  const [formSettings, setFormSettings] = useState([]);

  useEffect(() => {
    const products = orderFormData?.formProducts?.[orderFormId];
    const styles = orderFormData?.formStyles?.[orderFormId];
    const table = orderFormData?.formTable?.[orderFormId];
    const settings = orderFormData?.formSettings?.[orderFormId];
    if (
      typeof orderFormData?.formProducts?.[orderFormId]?.variations !==
      "undefined"
    )
      setFormVariations(orderFormData?.formProducts?.[orderFormId]?.variations);
  }, [orderFormData?.formProducts?.[orderFormId]?.variations]);

  useEffect(() => {
    if (
      typeof orderFormData?.formProducts?.[orderFormId]?.fetchingVariations !==
      "undefined"
    )
      setFetchingVariations(
        orderFormData?.formProducts?.[orderFormId]?.fetchingVariations
      );
  }, [orderFormData?.formProducts?.[orderFormId]?.fetchingVariations]);

  useEffect(() => {
    const products = orderFormData?.formProducts?.[orderFormId];
    const styles = orderFormData?.formStyles?.[orderFormId];
    const table = orderFormData?.formTable?.[orderFormId];
    const settings = orderFormData?.formSettings?.[orderFormId];
    if (
      typeof products !== "undefined" &&
      typeof products?.fetching !== "undefined"
    )
      setFetching(products?.fetching);

    if (
      typeof products !== "undefined" &&
      typeof products?.products !== "undefined"
    )
      setFormProducts(products?.products);

    if (
      typeof products !== "undefined" &&
      typeof products?.lazy_load_variations_data !== "undefined"
    )
      setFormLazyLoadVariationsData(products?.lazy_load_variations_data);

    if (typeof styles !== "undefined") setFormStyles(styles);

    if (typeof table !== "undefined") setFormTable(table);

    if (typeof settings !== "undefined") setFormSettings(settings);
  }, [orderFormData, orderFormId]);

  return {
    fetching,
    fetchingVariations,
    formProducts,
    formVariations,
    formLazyLoadVariationsData,
    formStyles,
    formTable,
    formSettings
  };
}
