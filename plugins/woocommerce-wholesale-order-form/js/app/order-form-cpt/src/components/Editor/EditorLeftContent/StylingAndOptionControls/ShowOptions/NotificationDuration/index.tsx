import { useEffect, useState } from "react";
import { Input } from "antd";

// Redux
import { connect } from "react-redux";

const NotificationDuration = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    notificationDurationStrings,
  } = props;

  const { label, description } = notificationDurationStrings;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "notificationDuration",
      extra: "",
    }) ?? 10
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "notificationDuration",
        extra: "",
      }) ?? 10
    );
  }, [id]);

  return (
    <div className="notification-duration">
      <label htmlFor="notification-duration">{label}:</label>
      <Input
        value={value ? value : ""}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              notificationDuration: value,
            },
          });
          setValue(value);
        }}
      />
      <em>{description}</em>
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  notificationDurationStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .notification_duration,
});

export default connect(mapStateToProps)(NotificationDuration);
