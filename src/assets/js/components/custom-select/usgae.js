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

const productSelector = new EntitySelector(".product-selector", {
  entityType: "product",
  apiEndpoint: "/api/products/search",
  cardRenderer: (card, product) => {
    // Custom product card rendering
    const img = card.querySelector(".img-container img");
    if (img && product.image) img.src = product.image;

    const title = card.querySelector(".product-info__title");
    if (title) title.textContent = product.name;

    const sku = card.querySelector(".product-info__sku");
    if (sku) sku.textContent = product.sku;

    const price = card.querySelector(".product-info__price");
    if (price) price.textContent = `$${product.price}`;
  },
  onSelect: (value, text, item) => {
    console.log("Product selected:", item);
  }
});
productSelector.init();

const categorySelector = new EntitySelector(".category-selector", {
  entityType: "category",
  apiEndpoint: "/api/categories/search",
  cardRenderer: (card, category) => {
    // Custom category card rendering
    const title = card.querySelector(".category-info__title");
    if (title) title.textContent = category.name;

    const slug = card.querySelector(".category-info__slug");
    if (slug) slug.textContent = category.slug;
  },
  itemFormatter: (item) => {
    return `${item.name} (${item.slug})`;
  }
});
categorySelector.init();

const brandSelector = new EntitySelector(".brand-selector", {
  entityType: "brand",
  apiEndpoint: "/api/brands/search",
  cardRenderer: (card, brand) => {
    const title = card.querySelector(".brand-info__title");
    if (title) title.textContent = brand.name;

    const website = card.querySelector(".brand-info__website");
    if (website) website.textContent = brand.website;
  },
  onSelect: (value, text, item) => {
    console.log("Brand selected:", item);
  }
});
brandSelector.init();

function getCustomSelectConfigs() {
  return [
    {
      selector: ".input-field",
      apiEndpoint: "/small-banner-search/load-products",
      placeholder: "Select parent category...",
      fieldName: "parent_id",
      itemFormatter: (item) => `${item.name} (${item.slug || item.id})`,
      onSelect: (value, text, item) => {
        this.logger.debug("Parent category selected:", { value, text });
      }
    },
    {
      selector: ".brand-selector", // If you have brand selector
      apiEndpoint: "/api/brands/search",
      placeholder: "Select brand...",
      fieldName: "brand_id",
      onSelect: (value, text, item) => {
        this.logger.debug("Brand selected:", { value, text });
      }
    }
  ];
}
