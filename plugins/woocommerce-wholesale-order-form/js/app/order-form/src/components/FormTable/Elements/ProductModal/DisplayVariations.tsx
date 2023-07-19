import ComboVariationsDropdown from "../shared/ComboVariationsDropdown";
import StandardVariationsDropdown from "../shared/StandardVariationsDropdown";
import { Spin } from "antd";

const DisplayVariations = (props: any) => {
  const { orderFormData, orderFormId, product, getPropValue } = props;

  if (product.type !== "variable") return <></>;

  const fetchingVariations =
    orderFormData?.formProducts?.[orderFormId]?.["fetchingVariations"];

  const variations =
    orderFormData.formProducts[orderFormId]["variations"][product.id];
  const formStyles = orderFormData.formStyles[orderFormId];

  if (variations === undefined || variations.length <= 0) return <></>;

  const variationSelector =
    getPropValue({
      formStyles,
      item: "variation-dropdown",
      prop: "variationSelectorStyle"
    }) || "combo";

  const sharedProps = {
    ...props,
    variations
  };

  return fetchingVariations === true ? (
    <Spin />
  ) : (
    <div style={{ margin: "1rem 0rem", display: "grid" }}>
      {variationSelector === "combo" ? (
        <ComboVariationsDropdown {...sharedProps} />
      ) : (
        <StandardVariationsDropdown {...sharedProps} />
      )}
    </div>
  );
};

export default DisplayVariations;
