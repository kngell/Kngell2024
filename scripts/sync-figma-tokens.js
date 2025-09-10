const fs = require("fs");
const path = require("path");
const axios = require("axios");
require("dotenv").config();

const FIGMA_TOKEN = process.env.FIGMA_TOKEN;
const FIGMA_FILE_KEY = process.env.FIGMA_FILE_KEY;
const OUTPUT_FILE = path.resolve(__dirname, "_figma-tokens.scss");

if (!FIGMA_TOKEN || !FIGMA_FILE_KEY) {
  console.error("❌ FIGMA_TOKEN or FIGMA_FILE_KEY missing in .env");
  process.exit(1);
}

async function fetchFigmaVariables() {
  try {
    const res = await axios.get(
      `https://api.figma.com/v1/files/${FIGMA_FILE_KEY}/variables/local`,
      {
        headers: { "X-Figma-Token": FIGMA_TOKEN },
      },
    );
    return res.data.meta.variables || [];
  } catch (err) {
    console.error("❌ Error fetching Figma variables:", err.message);
    if (err.response) {
      console.error("Response status:", err.response.status);
      console.error("Response data:", err.response.data);
    }
    process.exit(1);
  }
}

async function fetchVariableCollections() {
  try {
    const res = await axios.get(
      `https://api.figma.com/v1/files/${FIGMA_FILE_KEY}/variable_collections/local`,
      {
        headers: { "X-Figma-Token": FIGMA_TOKEN },
      },
    );
    return res.data.meta.variableCollections || [];
  } catch (err) {
    console.error("❌ Error fetching variable collections:", err.message);
    process.exit(1);
  }
}

// Convert variable value to CSS-friendly format
function formatVariableValue(value, variableType) {
  if (!value) return "null";

  switch (variableType) {
    case "COLOR":
      return rgbaToHex(value);
    case "FLOAT":
      return `${value}px`;
    case "STRING":
      return `"${value}"`;
    default:
      return value;
  }
}

// Convert RGBA to HEX
function rgbaToHex(color) {
  if (!color) return "null";

  const to255 = (v) => Math.round(v * 255);
  const r = to255(color.r);
  const g = to255(color.g);
  const b = to255(color.b);

  if (color.a !== undefined && color.a < 1) {
    const a = Math.round(color.a * 255);
    return `#${r.toString(16).padStart(2, "0")}${g.toString(16).padStart(2, "0")}${b
      .toString(16)
      .padStart(2, "0")}${a.toString(16).padStart(2, "0")}`;
  }
  return `#${r.toString(16).padStart(2, "0")}${g.toString(16).padStart(2, "0")}${b
    .toString(16)
    .padStart(2, "0")}`;
}

// Clean variable names for SCSS
function cleanName(name) {
  return name
    .toLowerCase()
    .replace(/\s+/g, "-")
    .replace(/[^a-z0-9-]/g, "");
}

// Generate SCSS content
function generateScss(variables, collections) {
  let scssContent = "// Auto-generated from Figma Variables\n\n";

  // Create collections map
  const collectionsMap = {};
  collections.forEach((collection) => {
    collectionsMap[collection.id] = {
      name: cleanName(collection.name),
      modes: collection.modes,
    };
  });

  // Group variables by collection
  const groupedVars = {};
  variables.forEach((variable) => {
    const collection = collectionsMap[variable.variableCollectionId];
    if (!collection) return;

    const collectionName = collection.name;
    if (!groupedVars[collectionName]) {
      groupedVars[collectionName] = {};
    }

    // Get the first mode value (you might want to handle multiple modes differently)
    const modeId = collection.modes[0].modeId;
    const value = variable.valuesByMode[modeId];

    groupedVars[collectionName][cleanName(variable.name)] = {
      value: formatVariableValue(value, variable.resolvedType),
      type: variable.resolvedType,
    };
  });

  // Generate SCSS variables
  for (const [collectionName, vars] of Object.entries(groupedVars)) {
    scssContent += `// ${collectionName} variables\n`;
    for (const [varName, varData] of Object.entries(vars)) {
      scssContent += `$${collectionName}-${varName}: ${varData.value};\n`;
    }
    scssContent += "\n";
  }

  return scssContent;
}

// Main function
(async () => {
  console.log("📡 Fetching Figma variables...");
  const variables = await fetchFigmaVariables();

  console.log("📡 Fetching variable collections...");
  const collections = await fetchVariableCollections();

  console.log(`🎨 Found ${variables.length} variables across ${collections.length} collections`);

  console.log("💾 Generating SCSS file...");
  const scssContent = generateScss(variables, collections);
  fs.writeFileSync(OUTPUT_FILE, scssContent, "utf-8");

  console.log("✅ SCSS tokens written to", OUTPUT_FILE);
})();
