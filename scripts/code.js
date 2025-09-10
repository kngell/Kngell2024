// code.js
figma.showUI(__html__, { width: 480, height: 600 });

function toKebab(str) {
  return str
    .replace(/_/g, "-")
    .replace(/([a-z])([A-Z])/g, "$1-$2")
    .toLowerCase();
}
function toSnake(str) {
  return str
    .replace(/-/g, "_")
    .replace(/([a-z])([A-Z])/g, "$1_$2")
    .toLowerCase();
}
function cleanKey(key, caseType) {
  let k = key.replace(/^[0-9]+-/, ""); // Remove leading numbers
  k = caseType === "snake" ? toSnake(k) : toKebab(k);
  return k;
}

// Extract variables and styles
function extractTokens(caseType) {
  var variables = {};
  var styles = { colors: {}, text: {}, effect: {}, grid: {} };

  // --- Variables (Collections) ---
  for (var collection of figma.variables.getLocalVariableCollections()) {
    var colName = cleanKey(collection.name, caseType);
    variables[colName] = {};
    for (var mode of collection.modes) {
      variables[colName][cleanKey(mode.name, caseType)] = {};
      for (var v of collection.variableIds) {
        var variable = figma.variables.getVariableById(v);
        if (variable && variable.resolvedType !== "alias") {
          var name = cleanKey(variable.name, caseType);
          variables[colName][cleanKey(mode.name, caseType)][name] =
            variable.valuesByMode[mode.modeId];
        }
      }
    }
  }

  // --- Styles (Groups) ---
  var allStyles = figma
    .getLocalPaintStyles()
    .concat(figma.getLocalTextStyles(), figma.getLocalEffectStyles(), figma.getLocalGridStyles());
  for (var style of allStyles) {
    var group = style.type.toLowerCase();
    var groupName = cleanKey(style.groupName || "default", caseType);
    if (!styles[group][groupName]) styles[group][groupName] = {};
    var styleName = cleanKey(style.name, caseType);
    // Value extraction (simplified, expand as needed)
    var value =
      style.type === "TEXT"
        ? {
            "font-family": style.fontName.family,
            "font-size": style.fontSize + "px",
            "line-height":
              style.lineHeight.unit === "PIXELS"
                ? (style.lineHeight.value / style.fontSize).toFixed(2)
                : style.lineHeight.value,
          }
        : style.type === "PAINT"
        ? style.paints[0].color
        : style;
    styles[group][groupName][styleName] = value;
  }

  return { variables: variables, styles: styles };
}

// Handle UI requests
figma.ui.onmessage = (msg) => {
  if (msg.type === "generate") {
    var caseType = msg.caseType || "kebab";
    var tokens = extractTokens(caseType);
    figma.ui.postMessage({ type: "tokens", tokens: tokens });
  }
};
