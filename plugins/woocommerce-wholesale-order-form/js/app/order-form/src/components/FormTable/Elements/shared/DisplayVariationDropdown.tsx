import ComboVariationsDropdown from "components/FormTable/Elements/shared/ComboVariationsDropdown";
import StandardVariationsDropdown from "components/FormTable/Elements/shared/StandardVariationsDropdown";

const DisplayVariationDropdown = (props: any) => {
  const { variationSelector, variations } = props;

  if (typeof variations === "undefined") return <></>;

  return (
    <div className={variationSelector} style={{ display: "grid" }}>
      {variationSelector === "combo" ? (
        <ComboVariationsDropdown {...props} variations={variations} />
      ) : (
        <StandardVariationsDropdown {...props} variations={variations} />
      )}
    </div>
  );
};

export default DisplayVariationDropdown;
