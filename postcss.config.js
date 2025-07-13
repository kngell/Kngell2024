const postcssPresetEnv = require("postcss-preset-env");
// const cssnano = require("cssnano");
const autoprefixer = require("autoprefixer");
module.exports = {
  plugins: [
    postcssPresetEnv({
      /* pluginOptions */
      stage: 4,
      autoprefixer: {
        grid: "autoplace",
        cascade: false,
      },
    }),
    // autoprefixer({ grid: "autoplace", cascade: false }),
    // cssnano({
    //   preset: "default",
    // }),
  ],
};
