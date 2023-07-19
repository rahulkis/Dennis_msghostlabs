import { Skeleton, Space, Table } from "antd";
import { useLocation } from "react-router-dom";
import { LoadingOutlined } from "@ant-design/icons";

const LoadingSkeleton = (props: any) => {
  const params = useLocation().search;
  const urlParams = new URLSearchParams(params);

  const subPage = urlParams.get("sub-page") ?? "";
  const postID = urlParams.get("post") || 0;

  const columns = [
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "name",
      key: "name",
      className: "name"
    },
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "shortcode",
      key: "shortcode"
    },
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "locations",
      key: "locations",
      className: "locations"
    },
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "status",
      key: "status"
    },
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "",
      key: "x"
    }
  ];

  if (subPage !== "" && postID > 0)
    return <LoadingOutlined style={{ fontSize: 24 }} spin />;
  else
    return (
      <div>
        <Space>
          <Skeleton.Button style={{ width: 200 }} active={true} />
          <Skeleton.Button style={{ width: 100 }} active={true} size="large" />
        </Space>
        <br />
        <br />
        <Skeleton active paragraph={{ rows: 3 }} title={false} />
        <br />
        <br />
        <Table loading={true} columns={columns} />
      </div>
    );
};

export default LoadingSkeleton;
