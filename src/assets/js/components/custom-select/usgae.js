// That's all it takes!
getCustomSelectConfigs() {
  return [{
    selector: ".input-field.custom-select",
    dataSource: "/admin/small-banner-search/load-products",
    placeholder: "Search Product by name or SKU...",
    fieldName: "product_id"
  }];
}

initSpecificComponents() {
  this.productCard = new ProductCard(".form-section.product-relationship .form-section__body", {
    customSelectSelector: ".input-field.custom-select"
  });
}