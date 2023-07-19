import { Button, Spin } from "antd";

import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { setupWizardActions } from "store/actions";
import { lazy, Suspense, useEffect, useState } from "react";

const { generateAPIKeys } = setupWizardActions;

const DisplayKeys = lazy(() => import("./DisplayKeys"));

const SecondStep = (props: any) => {
  const { setupWizardSteps, actions, index } = props;
  const { generateAPIKeys } = actions;
  const [displayKeys, setDisplayKeys] = useState(false);
  const [step2Data, setStep2Data] = useState([]);

  useEffect(() => {
    const step2 = setupWizardSteps.find((x: any) => x.id === "step2");
    if (typeof step2 !== "undefined") {
      setDisplayKeys(step2?.data?.is_valid);
      setStep2Data(step2);
    }
  }, [setupWizardSteps]);

  const autoGenerateKeys = () => {
    generateAPIKeys();
  };

  return (
    <div>
      <h1>{setupWizardSteps?.[index]?.["page_title"]}</h1>
      <div
        dangerouslySetInnerHTML={{
          __html: setupWizardSteps?.[index]?.["content"],
        }}
      />

      <div id="api_keys" style={{ display: "flex", justifyContent: "center" }}>
        {!displayKeys ? (
          <Button
            type="primary"
            onClick={autoGenerateKeys}
            style={{ marginTop: "20px" }}
          >
            {setupWizardSteps?.[index]?.["auto_generate"]}
          </Button>
        ) : (
          <></>
        )}
        <Suspense
          fallback={
            <div className="loading-spinner" style={{ marginTop: "40px" }}>
              <Spin /> &nbsp;{setupWizardSteps?.[index]?.["loading"]}
            </div>
          }
        >
          <DisplayKeys
            displayKeys={displayKeys}
            step2Data={step2Data}
            setupWizardSteps={setupWizardSteps}
            index={index}
          />
        </Suspense>
      </div>
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  setupWizardSteps: store.setupWizard.steps,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      generateAPIKeys,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(SecondStep);
