import { CheckOutlined } from "@ant-design/icons";
import { List } from "antd";
import { connect } from "react-redux";
import { useEffect, useState } from "react";

const FirstStep = (props: any) => {
  const { setupWizardSteps, index } = props;
  const [perks, setPerks] = useState([]);

  useEffect(() => {
    setPerks(setupWizardSteps?.[index]?.["perks"] ?? []);
  }, [setupWizardSteps, index]);

  return (
    <div>
      <h1>{setupWizardSteps?.[index]?.["page_title"]}</h1>
      <div
        dangerouslySetInnerHTML={{
          __html: setupWizardSteps?.[index]?.["content"]
        }}
      />
      <List
        bordered
        dataSource={perks}
        renderItem={(item) => (
          <List.Item>
            <CheckOutlined style={{ color: "green" }} />
            {` ${item}`}
          </List.Item>
        )}
      />
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  setupWizardSteps: store.setupWizard.steps
});

export default connect(mapStateToProps)(FirstStep);
