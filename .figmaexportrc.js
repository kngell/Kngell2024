const asSass = require("@figma-export/output-styles-as-sass").default;

module.exports = {
  token: "",
  fileId: "6Oj57EATtfYnNrWHH3x3e6", //"aSskSubvKYW8dBMgznxrSR",
  commands: [
    [
      "styles",
      {
        outputters: [asSass({ output: "./tokens" })],
      },
    ],
  ],
};
