const { merge } = require("webpack-merge");
const { commonConfig } = require("./webpack/commonConfig");
const { viewsConfig } = require("./webpack/viewsConfig");
const { assetsConfig } = require("./webpack/assetsConfig");
require("dotenv").config();

module.exports = () => {
  switch (process.env.NODE_ENV) {
    case "development":
      return [merge(viewsConfig, commonConfig), merge(assetsConfig, commonConfig)];

    case "production":
      console.log("production");
      break;

    default:
      throw new Error("No matching configuration was found!");
  }
};
