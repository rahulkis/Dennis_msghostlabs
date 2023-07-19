import { Alert, Form, Input } from "antd";

const DisplayKeys = (props: any) => {
  const { displayKeys, step2Data, setupWizardSteps, index } = props;

  return displayKeys ? (
    <Form
      style={{ marginTop: "40px", width: "80%" }}
      name="basic"
      labelCol={{ span: 6 }}
      wrapperCol={{ span: 16 }}
      initialValues={{
        consumer_key: step2Data?.data?.consumer_key,
        consumer_secret: step2Data?.data?.consumer_secret
      }}
    >
      <Alert
        style={{ marginBottom: "30px" }}
        message={setupWizardSteps?.[index]?.["display_key"]?.["message"]}
        type="success"
        showIcon
      />
      <Form.Item
        label={setupWizardSteps?.[index]?.["display_key"]?.["consumer_key"]}
        name="consumer_key"
      >
        <Input disabled />
      </Form.Item>

      <Form.Item
        label={setupWizardSteps?.[index]?.["display_key"]?.["consumer_secret"]}
        name="consumer_secret"
      >
        <Input.Password disabled />
      </Form.Item>
    </Form>
  ) : (
    <></>
  );
};

export default DisplayKeys;
