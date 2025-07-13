const { merge } = require("webpack-merge");
const { commonConfig } = require("./webpack/commonConfig");
const { viewsConfig } = require("./webpack/viewsConfig");
const { assetsConfig } = require("./webpack/assetConfig.v2");
require("dotenv").config();

module.exports = () => {
  switch (process.env.NODE_ENV) {
    case "development":
      return merge(assetsConfig, commonConfig, viewsConfig);

    case "production":
      console.log("production");
      break;

    default:
      throw new Error("No matching configuration was found!");
  }
};
