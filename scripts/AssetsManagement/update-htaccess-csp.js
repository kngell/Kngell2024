// update-htaccess-csp.js
// Usage: node scripts/update-htaccess-csp.js

const fs = require("fs");
const path = require("path");

const htaccessPath = path.resolve(process.cwd, ".htaccess");
const cspJsonPath = path.resolve(process.cwd, "csp.dev.json");

if (!fs.existsSync(htaccessPath)) {
  console.error(".htaccess not found!");
  process.exit(1);
}
if (!fs.existsSync(cspJsonPath)) {
  console.error("csp.dev.json not found!");
  process.exit(1);
}

const cspJson = JSON.parse(fs.readFileSync(cspJsonPath, "utf-8"));
const csp = Object.entries(cspJson)
  .map(([k, v]) => `${k} ${v}`)
  .join("; ");

let htaccess = fs.readFileSync(htaccessPath, "utf-8");

// Replace CSP placeholder or existing CSP line
const cspRegex = /Header set Content-Security-Policy ".*"/;
const newCspLine = `Header set Content-Security-Policy "${csp}"`;

if (cspRegex.test(htaccess)) {
  htaccess = htaccess.replace(cspRegex, newCspLine);
} else {
  // Insert after Referrer-Policy header
  htaccess = htaccess.replace(
    /(Header always set Referrer-Policy.*\n)/,
    `$1    ${newCspLine}\n`
  );
}

fs.writeFileSync(htaccessPath, htaccess, "utf-8");
console.log("Updated .htaccess with CSP from csp.dev.json");
