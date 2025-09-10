const asSass = require("@figma-export/output-styles-as-sass").default;

module.exports = {
  token: "figd_ZPipZSHndwJS10m_FXzkVcpjud1dsuGL6MOEd4lJ",
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
