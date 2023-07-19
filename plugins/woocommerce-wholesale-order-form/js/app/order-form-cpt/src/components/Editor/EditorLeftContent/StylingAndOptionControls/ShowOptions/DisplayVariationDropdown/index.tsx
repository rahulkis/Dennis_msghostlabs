import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const DisplayVariationDropdown = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    displayVariationDropdownStrings,
  } = props;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "displayVariationDropdown",
      extra: "",
    })
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "displayVariationDropdown",
        extra: "",
      })
    );
  }, [id]);

  return (
    <div className="display-variation-dropdown-option">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              displayVariationDropdown: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {displayVariationDropdownStrings?.label}
      </Checkbox>
      <em>{displayVariationDropdownStrings?.description_1}</em>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  displayVariationDropdownStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .display_variation_dropdown,
});

export default connect(mapStateToProps)(DisplayVariationDropdown);
