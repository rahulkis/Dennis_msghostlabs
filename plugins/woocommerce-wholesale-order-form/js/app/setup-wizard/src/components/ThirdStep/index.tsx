import { lazy } from "react";
import { connect } from "react-redux";
const GenerateOrderForm = lazy(() => import("./GenerateOrderForm"));

const ThirdStep = (props: any) => {
  const { setupWizardSteps, index } = props;
  return (
    <div>
      <h1>{setupWizardSteps?.[index]?.["page_title"]}</h1>
      <div
        dangerouslySetInnerHTML={{
          __html: setupWizardSteps?.[index]?.["content"],
        }}
      />

      <GenerateOrderForm index={index} />
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  setupWizardSteps: store.setupWizard.steps,
});

export default connect(mapStateToProps)(ThirdStep);
