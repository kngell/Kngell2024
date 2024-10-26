const { merge } = require("webpack-merge");
const { commonConfig } = require("./webpack/commonConfig");
const { viewsConfig } = require("./webpack/viewsConfig");

module.exports = () => {
  switch (process.env.NODE_ENV) {
    case "development":
      return [merge(viewsConfig, commonConfig)];

    case "production":
      console.log("production");
      break;

    default:
      throw new Error("No matching configuration was found!");
  }
};
