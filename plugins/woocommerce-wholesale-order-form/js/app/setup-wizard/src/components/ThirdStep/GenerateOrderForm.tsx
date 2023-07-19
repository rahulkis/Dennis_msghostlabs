import { Spin, Table } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { setupWizardActions } from "store/actions";
import { useEffect, useState } from "react";
import useGeneratedOrderForms from "helpers/useGeneratedOrderForms";
import { StarOutlined } from "@ant-design/icons";

const { migrateOldForms } = setupWizardActions;

const GenerateOrderForms = (props: any) => {
  const { generatedOrderForms, actions, setupWizardSteps, i18n, index } = props;
  const { migrateOldForms } = actions;
  const [fetching, setFetching] = useState(true);
  const [dataSource, columns] = useGeneratedOrderForms(
    generatedOrderForms,
    setupWizardSteps,
    index
  );
  const [i18nTexts, setI18nTexts] = useState(i18n);

  useEffect(() => {
    setI18nTexts(i18n);
  }, [i18n]);

  useEffect(() => {
    migrateOldForms({
      successCB: (response: any) => {
        setFetching(false);
      },
      failCB: () => {
        console.log("Data cannot be fetched!");
        setFetching(false);
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return fetching ? (
    <div className="loading-spinner" style={{ marginTop: "40px" }}>
      <Spin /> &nbsp;{setupWizardSteps?.[index]?.["generating_forms"]}
    </div>
  ) : (
    <>
      <div
        dangerouslySetInnerHTML={{
          __html: setupWizardSteps?.[index]?.["generated_forms"]
        }}
      />
      <Table
        className="generated-order-forms"
        columns={columns}
        dataSource={dataSource}
        pagination={false}
        bordered
        style={{ marginBottom: "10px" }}
      />
      <StarOutlined style={{ color: "#fec400" }} />
      &nbsp;
      <a href={i18nTexts?.["pro_tip_link"]} target="_blank" rel="noreferrer">
        <span
          style={{ fontWeight: 500 }}
          dangerouslySetInnerHTML={{
            __html: setupWizardSteps?.[index]?.["pro_tip"]
          }}
        />
      </a>
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  setupWizardSteps: store.setupWizard.steps,
  generatedOrderForms: store.setupWizard.generatedOrderForms,
  i18n: store?.setupWizard?.i18n
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      migrateOldForms
    },
    dispatch
  )
});

export default connect(mapStateToProps, mapDispatchToProps)(GenerateOrderForms);
