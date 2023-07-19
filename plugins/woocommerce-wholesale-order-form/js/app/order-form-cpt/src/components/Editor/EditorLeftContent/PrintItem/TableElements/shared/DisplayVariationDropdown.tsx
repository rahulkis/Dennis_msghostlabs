import StandardVariationsDropdown from "components/Editor/EditorLeftContent/PrintItem/TableElements/shared/StandardVariationsDropdown";
import ComboVariationsDropdown from "components/Editor/EditorLeftContent/PrintItem/TableElements/shared/ComboVariationsDropdown";

const DisplayVariationDropdown = (props: any) => {
  const { variationSelector, orderForm, products, d, loadMoreVariations } =
    props;

  return (
    <>
      {variationSelector === null || variationSelector === "combo" ? (
        <ComboVariationsDropdown
          orderForm={orderForm}
          products={products}
          product={d}
          variations={products.variations}
          loadMoreVariations={loadMoreVariations}
        />
      ) : (
        <StandardVariationsDropdown
          roducts={products}
          product={d}
          variations={products.variations}
        />
      )}
    </>
  );
};
export default DisplayVariationDropdown;
