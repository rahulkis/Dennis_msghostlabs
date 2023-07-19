import { Suspense, lazy } from "react";
import { Spin } from "antd";

import "./App.scss";
import "antd/dist/antd.css";
const MigrationSteps = lazy(() => import("components/MigrationSteps"));

const App = (props: any) => {
  return (
    <Suspense
      fallback={
        <div className="loading-spinner">
          <Spin /> &nbsp;Loading...
        </div>
      }
    >
      <MigrationSteps />
    </Suspense>
  );
};

export default App;
