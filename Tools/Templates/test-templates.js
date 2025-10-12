// Tools/Templates/test-templates.js
const path = require("path");
const getter = require(path.resolve(__dirname, "getter-template.js"));
const setter = require(path.resolve(__dirname, "setter-template.js"));

// Example property matching your `imageGallery` case
const prop = {
  name: "imageGallery",
  nameUcFirst: "ImageGallery",
  phpType: "?array", // indicates nullable array
  className: "Product", // optional; used for setter docblock if present
};

console.log("--- GETTER ---");
console.log(getter(prop));
console.log("--- SETTER ---");
console.log(setter(prop));
