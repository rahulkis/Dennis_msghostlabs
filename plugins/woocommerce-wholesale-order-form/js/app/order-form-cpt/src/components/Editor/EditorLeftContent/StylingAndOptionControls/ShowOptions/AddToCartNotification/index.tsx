import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";
import NotificationDuration from "../NotificationDuration";

const AddToCartNotification = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    addToCartNotificationStrings,
  } = props;

  const { label, description } = addToCartNotificationStrings;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "addToCartNotification",
      extra: "",
    }) ?? true
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "addToCartNotification",
        extra: "",
      }) ?? true
    );
  }, [id]);

  return (
    <>
      <div className="add-to-cart-notification">
        <Checkbox
          checked={value}
          onChange={(e: any) => {
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: {
                addToCartNotification: e.target.checked,
              },
            });
            setValue(e.target.checked);
          }}
        >
          {label}
        </Checkbox>
        <em>{description}</em>
      </div>
      {value === true ? <NotificationDuration {...props} /> : ""}
    </>
  );
};

const mapStateToProps = (store: any) => ({
  addToCartNotificationStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .add_to_cart_notification,
});

export default connect(mapStateToProps)(AddToCartNotification);
