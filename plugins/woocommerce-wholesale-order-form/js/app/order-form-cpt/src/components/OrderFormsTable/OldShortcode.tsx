import { Popover } from "antd";
import { useState } from "react";
import { QuestionCircleOutlined } from "@ant-design/icons";

// Locations Column Data
const OldShortcode = (props: any) => {
  const { old_shortcode, old_shortcode_desc } = props;
  const [popupVisibility, setPopupVisibility] = useState(false);

  const content = (
    <>
      <p>{old_shortcode_desc}</p>
      <code>{old_shortcode}</code>
    </>
  );

  if (old_shortcode !== "") {
    return (
      <Popover
        content={content}
        trigger="click"
        visible={popupVisibility}
        onVisibleChange={(visible) => setPopupVisibility(visible)}
      >
        <QuestionCircleOutlined />
      </Popover>
    );
  } else return <></>;
};

export default OldShortcode;
