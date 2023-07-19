import { Space, Tooltip } from "antd";
import { useEffect, useState } from "react";
import { ISetupWizardOptions } from "types/index";
import { QuestionCircleOutlined } from "@ant-design/icons";
declare var setup_wizard_options: ISetupWizardOptions;

const useGeneratedOrderForms = (
  generatedOrderForms: any,
  setupWizardSteps: any,
  index: number
) => {
  const [data, setData] = useState([]);
  let columns: any = [];

  // FOR MIGRATION
  if (Boolean(setup_wizard_options.migration)) {
    columns = [
      {
        title:
          setupWizardSteps?.[index]?.["generated_forms_table"]?.[
            "old_shortcode"
          ],
        dataIndex: "old",
        key: "old"
      },
      {
        title:
          setupWizardSteps?.[index]?.["generated_forms_table"]?.[
            "new_shortcode"
          ],
        dataIndex: "new",
        key: "new"
      },
      {
        title: setupWizardSteps?.[index]?.["generated_forms_table"]?.["action"],
        key: "action",
        render: (text: any, record: any) => (
          <Space size="middle">
            <a href={record?.edit_form ?? ""} target="_blank" rel="noreferrer">
              {
                setupWizardSteps?.[index]?.["generated_forms_table"]?.[
                  "edit_form"
                ]
              }
            </a>
            <a href={record?.view_form ?? ""} target="_blank" rel="noreferrer">
              {
                setupWizardSteps?.[index]?.["generated_forms_table"]?.[
                  "view_form"
                ]
              }
            </a>
          </Space>
        )
      }
    ];
  } else {
    // FOR FIRST INSTALLS
    columns = [
      {
        title:
          setupWizardSteps?.[index]?.["generated_forms_table"]?.["shortcode"],
        dataIndex: "shortcode",
        key: "shortcode"
      },
      {
        title: setupWizardSteps?.[index]?.["generated_forms_table"]?.["action"],
        key: "action",
        render: (text: any, record: any) => (
          <Space size="middle">
            <a href={record?.edit_form ?? ""} target="_blank" rel="noreferrer">
              {
                setupWizardSteps?.[index]?.["generated_forms_table"]?.[
                  "edit_form"
                ]
              }
            </a>
            <a href={record?.view_form ?? ""} target="_blank" rel="noreferrer">
              {
                setupWizardSteps?.[index]?.["generated_forms_table"]?.[
                  "view_form"
                ]
              }
            </a>
          </Space>
        )
      }
    ];
  }

  useEffect(() => {
    let data: any = [];

    if (generatedOrderForms.length > 0) {
      if (Boolean(setup_wizard_options.migration)) {
        generatedOrderForms.forEach((item: any, key: number, arr: any) => {
          data = [
            ...data,
            {
              key: key,
              old: item?.shortcode,
              new: (
                <>
                  {`${item?.new_shortcode} ${
                    item.default
                      ? setupWizardSteps?.[index]?.["generated_forms_table"]?.[
                          "default"
                        ]
                      : ""
                  }`}
                  &nbsp;
                  {item.default ? (
                    <Tooltip
                      title={
                        setupWizardSteps?.[index]?.["generated_forms_table"]?.[
                          "tooltip"
                        ]
                      }
                    >
                      <QuestionCircleOutlined />
                    </Tooltip>
                  ) : (
                    ""
                  )}
                </>
              ),
              edit_form: item?.edit_form,
              view_form: item?.view_form
            }
          ];
        });
      } else {
        generatedOrderForms.forEach((item: any, key: number, arr: any) => {
          data = [
            ...data,
            {
              key: key,
              shortcode: item?.shortcode,
              edit_form: item?.edit_form,
              view_form: item?.view_form
            }
          ];
        });
      }
    }

    setData(data);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [generatedOrderForms]);

  return [data, columns];
};

export default useGeneratedOrderForms;
