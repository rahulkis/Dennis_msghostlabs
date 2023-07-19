import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const OpenInNewTab = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    imageCLickActionStrings,
  } = props;

  const { open_new_tab, open_new_tab_desc } = imageCLickActionStrings;

  const [value, setValue] = useState(
    getPropValue({ styling, id, target, style: "openInNewTab", extra: "" }) ??
      true
  );

  useEffect(() => {
    setValue(
      getPropValue({ styling, id, target, style: "openInNewTab", extra: "" }) ??
        true
    );
  }, [id]);

  return (
    <div className="open-in-new-tab">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              openInNewTab: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {open_new_tab}
      </Checkbox>
      <em>{open_new_tab_desc}</em>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  imageCLickActionStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .image_click_action,
});

export default connect(mapStateToProps)(OpenInNewTab);
