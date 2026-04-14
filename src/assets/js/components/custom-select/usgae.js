// // Usage
const categorySelect = new CustomSelect(".input-field.custom-select", {
  dataSource: [
    { value: "1", label: "Electronics" },
    { value: "2", label: "Clothing" },
    { value: "3", label: "Books" }
  ],
  onSelect: (value, label) => console.log(`Selected: ${label}`)
});
categorySelect.init();

const productSelector = new ProductSelector(".custom-select-container");
productSelector.init();
