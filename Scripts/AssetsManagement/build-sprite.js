#!/usr/bin/env node

/**
 * SVG Sprite Builder
 * Run with: node build-sprite.js
 */

const fs = require("fs");
const path = require("path");

// Configuration
const config = {
  inputDir: "./src/assets/img/icons",
  outputFile: "./public/assets/img/icons-sprite.svg",
  prefix: "icon-", // Optional prefix for symbol IDs
};

function buildSprite() {
  console.log("🔨 Building SVG sprite...");

  // Check if input directory exists
  if (!fs.existsSync(config.inputDir)) {
    console.error(`❌ Input directory not found: ${config.inputDir}`);
    return;
  }

  // Get all SVG files
  const svgFiles = fs
    .readdirSync(config.inputDir)
    .filter((file) => file.endsWith(".svg"))
    .sort();

  if (svgFiles.length === 0) {
    console.error(`❌ No SVG files found in ${config.inputDir}`);
    return;
  }

  console.log(`📁 Found ${svgFiles.length} SVG files:`);
  svgFiles.forEach((file) => console.log(`   - ${file}`));

  // Start building the sprite
  let spriteContent = `<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">\n`;

  svgFiles.forEach((file) => {
    const filePath = path.join(config.inputDir, file);
    const fileName = path.basename(file, ".svg");
    const symbolId = config.prefix + fileName;

    try {
      // Read the SVG file
      let svgContent = fs.readFileSync(filePath, "utf8");

      // Extract viewBox from the original SVG
      const viewBoxMatch = svgContent.match(/viewBox="([^"]+)"/);
      const viewBox = viewBoxMatch ? viewBoxMatch[1] : "0 0 24 24";

      // Extract the inner content (everything between <svg> tags)
      const innerContent = svgContent
        .replace(/<svg[^>]*>/, "")
        .replace(/<\/svg>/, "")
        .trim();

      // Create symbol
      spriteContent += `  <!-- ${fileName} -->\n`;
      spriteContent += `  <symbol id="${symbolId}" viewBox="${viewBox}">\n`;
      spriteContent += `    ${innerContent}\n`;
      spriteContent += `  </symbol>\n\n`;

      console.log(`✅ Added: ${symbolId}`);
    } catch (error) {
      console.error(`❌ Error processing ${file}:`, error.message);
    }
  });

  spriteContent += `</svg>`;

  // Write the sprite file
  try {
    fs.writeFileSync(config.outputFile, spriteContent);
    console.log(`🎉 Sprite created successfully: ${config.outputFile}`);
    console.log(`📊 Total symbols: ${svgFiles.length}`);
  } catch (error) {
    console.error(`❌ Error writing sprite file:`, error.message);
  }
}

// Run the builder
buildSprite();
