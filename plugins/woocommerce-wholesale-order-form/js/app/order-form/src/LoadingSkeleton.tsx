import { Col, Row, Skeleton, Space, Table } from "antd";
const LoadingSkeleton = (props: any) => {
  const columns = [
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "name",
      key: "name",
      className: "name"
    },
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "sku",
      key: "sku"
    },
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "price",
      key: "price",
      className: "price"
    },
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "quantity",
      key: "quantity"
    },
    {
      title: <Skeleton.Button active={true} />,
      dataIndex: "",
      key: "x"
    }
  ];

  return (
    <Row>
      <Col span={24}>
        <Skeleton.Button style={{ width: 250 }} active={true} size="large" />
        <br />
        <br />
        <Space>
          <Skeleton.Button style={{ width: 250 }} active={true} />
          <Skeleton.Button style={{ width: 250 }} active={true} />
          <Skeleton.Button style={{ width: 150 }} active={true} />
          <Skeleton.Button style={{ width: 150 }} active={true} />
        </Space>
        <br />
        <br />
        <Table loading={true} columns={columns} />
      </Col>
    </Row>
  );
};

export default LoadingSkeleton;
