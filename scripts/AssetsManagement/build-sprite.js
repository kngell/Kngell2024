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
  prefix: "icon-",
  defaultViewBox: "0 0 24 24",
  preserveAttributes: ["fill", "stroke", "stroke-width", "stroke-linecap", "stroke-linejoin"]
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

      // Extract viewBox or dimensions
      let viewBox = config.defaultViewBox;
      const viewBoxMatch = svgContent.match(/viewBox="([^"]+)"/);
      if (viewBoxMatch) {
        viewBox = viewBoxMatch[1];
      } else {
        // Try to extract from width/height if viewBox not present
        const widthMatch = svgContent.match(/width="([^"]+)"/);
        const heightMatch = svgContent.match(/height="([^"]+)"/);
        if (widthMatch && heightMatch) {
          viewBox = `0 0 ${widthMatch[1]} ${heightMatch[1]}`;
        }
      }

      // Extract the inner content and preserve important attributes
      let innerContent = svgContent
        .replace(/<svg[^>]*>/, "")
        .replace(/<\/svg>/, "")
        .trim();

      // If the SVG has fill="currentColor" or stroke="currentColor", keep them
      // but if they're on the SVG tag, we need to move them to the inner elements

      // Extract SVG attributes to check for fill/stroke
      const svgAttrsMatch = svgContent.match(/<svg([^>]*)>/);
      let svgAttrs = {};
      if (svgAttrsMatch) {
        const attrs = svgAttrsMatch[1].match(/(\w+)="([^"]+)"/g) || [];
        attrs.forEach((attr) => {
          const [key, value] = attr.split("=");
          if (config.preserveAttributes.includes(key)) {
            svgAttrs[key] = value.replace(/"/g, "");
          }
        });
      }

      // If inner content has multiple elements, wrap them properly
      // Ensure path elements have necessary attributes if not present
      if (Object.keys(svgAttrs).length > 0) {
        // Add attributes to each path/element if they don't have them
        const elements = innerContent.match(/<[^>]+>/g) || [];
        const updatedElements = elements.map((el) => {
          let newEl = el;
          Object.entries(svgAttrs).forEach(([key, value]) => {
            if (!el.includes(`${key}=`)) {
              // Insert attribute before closing >
              newEl = newEl.replace(/>$/, ` ${key}="${value}">`);
            }
          });
          return newEl;
        });
        innerContent = updatedElements.join(" ");
      }

      // Create symbol
      spriteContent += `  <!-- ${fileName} -->\n`;
      spriteContent += `  <symbol id="${symbolId}" viewBox="${viewBox}">\n`;
      spriteContent += `    ${innerContent}\n`;
      spriteContent += `  </symbol>\n\n`;

      console.log(`✅ Added: ${symbolId} (viewBox: ${viewBox})`);
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
