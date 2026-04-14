const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

const cacheDir = path.resolve(process.cwd(), ".temp_cache");

function getCacheStats() {
  console.log("\n📦 FILESYSTEM CACHE STATISTICS");
  console.log("================================");

  if (!fs.existsSync(cacheDir)) {
    console.log("❌ Cache directory does not exist yet.");
    console.log("👉 Run: npm run build (first build)\n");
    return;
  }

  function getSize(dir) {
    let size = 0;
    const files = fs.readdirSync(dir, { withFileTypes: true });

    for (const file of files) {
      const filePath = path.join(dir, file.name);
      if (file.isDirectory()) {
        size += getSize(filePath);
      } else {
        size += fs.statSync(filePath).size;
      }
    }
    return size;
  }

  function countFiles(dir) {
    let count = 0;
    const files = fs.readdirSync(dir, { withFileTypes: true });
    for (const file of files) {
      const filePath = path.join(dir, file.name);
      if (file.isDirectory()) {
        count += countFiles(filePath);
      } else {
        count++;
      }
    }
    return count;
  }

  const totalSize = getSize(cacheDir);
  const sizeMB = (totalSize / 1024 / 1024).toFixed(2);
  const fileCount = countFiles(cacheDir);

  console.log(`📍 Location: ${cacheDir}`);
  console.log(`📊 Size: ${sizeMB} MB`);
  console.log(`📄 Files: ${fileCount}`);

  // Get disk usage with du command for verification
  try {
    const du = execSync(`du -sh "${cacheDir}"`).toString().trim();
    console.log(`💾 Disk usage: ${du.split("\t")[0]}`);
  } catch (e) {
    // ignore
  }
  // Add to your cache-monitor.js
  const phpCacheDir = path.resolve(process.cwd(), ".cache");
  if (fs.existsSync(phpCacheDir)) {
    const phpCacheSize = getSize(phpCacheDir);
    const phpCacheMB = (phpCacheSize / 1024 / 1024).toFixed(2);
    console.log(`📁 PHP Processor cache: ${phpCacheMB} MB`);
  }
  // Show cache contents
  const items = fs.readdirSync(cacheDir);
  if (items.length > 0) {
    console.log("\n📁 Cache contents:");
    items.forEach((item) => {
      const itemPath = path.join(cacheDir, item);
      const itemStats = fs.statSync(itemPath);
      if (itemStats.isDirectory()) {
        const itemSize = getSize(itemPath);
        const itemSizeMB = (itemSize / 1024 / 1024).toFixed(2);
        console.log(`   📂 ${item}/ (${itemSizeMB} MB)`);
      } else {
        const itemSizeKB = (itemStats.size / 1024).toFixed(1);
        console.log(`   📄 ${item} (${itemSizeKB} KB)`);
      }
    });
  } else {
    console.log("\n📁 Cache directory is empty");
    console.log("👉 Run: npm run build to populate cache");
  }
}

getCacheStats();
