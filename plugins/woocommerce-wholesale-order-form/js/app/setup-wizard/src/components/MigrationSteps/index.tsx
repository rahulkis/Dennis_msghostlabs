import { Steps, Button, message } from "antd";
import { useState, useEffect } from "react";
import FirstStep from "components/FirstStep";
import SecondStep from "components/SecondStep";
import ThirdStep from "components/ThirdStep";

import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { setupWizardActions } from "store/actions";

const { fetchSteps, setupDone } = setupWizardActions;
const { Step } = Steps;

const MigrationSteps = (props: any) => {
  const { setupWizardSteps, i18n, actions } = props;
  const [current, setCurrent] = useState(0);
  const [isDisabled, setIsDisabled] = useState(false);
  const [buttonDone, setButtonDone] = useState(false);
  const [i18nTexts, setI18nTexts] = useState(i18n);

  const next = () => {
    setCurrent(current + 1);
  };

  const [steps, setSteps] = useState<any>([]);

  useEffect(() => {
    setI18nTexts(i18n);
  }, [i18n]);

  useEffect(() => {
    actions.fetchSteps();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (current === 1) {
      const secondStep = setupWizardSteps.find((x: any) => x.id === "step2");
      if (typeof secondStep !== "undefined")
        setIsDisabled(!secondStep?.data?.is_valid);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [current, setupWizardSteps]);

  useEffect(() => {
    if (setupWizardSteps.length > 0) {
      let wizardSteps: any[] = [];

      setupWizardSteps.forEach((item: any, index: number, arr: any) => {
        switch (item.id) {
          case "step1":
            wizardSteps.push({
              title: item.title,
              content: <FirstStep index={index} />
            });
            break;
          case "step2":
            wizardSteps.push({
              title: item.title,
              content: <SecondStep index={index} />
            });
            break;
          case "step3":
            wizardSteps.push({
              title: item.title,
              content: <ThirdStep index={index} />
            });
            break;
        }
      });

      setSteps(wizardSteps);
    }
  }, [setupWizardSteps]);
  console.log(i18nTexts?.["need_support_link"]);
  return steps.length === 0 ? (
    <></>
  ) : (
    <>
      <Steps current={current}>
        {steps.map((item: any) => (
          <Step key={item.title} title={item.title} />
        ))}
      </Steps>
      <div className="steps-content">
        <div className="wws-logo">
          <img src={i18nTexts?.["wws_logo"]} alt="Wholesale Suite Logo" />
        </div>
        {steps[current].content}
      </div>
      <div
        className="steps-action"
        style={{ textAlign: "center", marginTop: "20px" }}
      >
        {current < steps.length - 1 && (
          <Button type="primary" onClick={() => next()} disabled={isDisabled}>
            {i18nTexts?.["continue"]}
          </Button>
        )}
        {current === steps.length - 1 && (
          <>
            <Button
              type="primary"
              loading={buttonDone}
              onClick={() => {
                setButtonDone(true);
                actions.setupDone({
                  successCB: (response: any) => {
                    message.success(i18nTexts?.["process_complete"]);
                    setTimeout(() => {
                      window.location.replace(response?.data?.data?.redirect);
                    }, 1500);
                  },
                  failCB: (response: any) => {
                    console.log(response);
                    message.error(i18nTexts?.["fail"]);
                  }
                });
              }}
            >
              {i18nTexts?.["done"]}
            </Button>
            <br />
            <a
              href={i18nTexts?.["need_support_link"]}
              target="_blank"
              rel="noreferrer"
              style={{
                marginTop: "100px",
                display: "inline-block",
                color: "gray"
              }}
            >
              <span
                dangerouslySetInnerHTML={{
                  __html: i18nTexts?.["need_support_text"]
                }}
              />
            </a>
          </>
        )}
      </div>
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  setupWizardSteps: store?.setupWizard?.steps,
  i18n: store?.setupWizard?.i18n
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      fetchSteps,
      setupDone
    },
    dispatch
  )
});

export default connect(mapStateToProps, mapDispatchToProps)(MigrationSteps);
