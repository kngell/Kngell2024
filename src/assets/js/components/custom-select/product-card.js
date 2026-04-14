import BrowserLogger from "js/core/utils/BrowserLogger";
const logger = new BrowserLogger("ProductCard");

export default class ProductCard {
  constructor(el) {
    this.root = el;
    this.productCard = el.querySelector(".product-card");

    if (!this.productCard) return;

    this.imgContainer = this.productCard.querySelector(".img-container");
    this.title = this.productCard.querySelector(".product-info__title");
    this.sku = this.productCard.querySelector(".product-info__sku");
    this.shortDescription = this.productCard.querySelector(".product-info__short-description");
    this.removeBtn = this.productCard.querySelector(".product-card__right button");

    this.handleRemove = this.handleRemove.bind(this);
    this.init();
  }

  init() {
    if (this.removeBtn) {
      this.removeBtn.addEventListener("click", this.handleRemove);
    }
  }

  handleRemove(event) {
    // Prevent form submission and stop event from bubbling to parent click listeners
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    this.clear();

    this.root.dispatchEvent(
      new CustomEvent("product-card:remove", {
        bubbles: true,
        detail: { originalElement: this.root }
      })
    );
  }

  render(productData) {
    if (!productData) return;

    this.renderImage(productData.image);

    if (this.title) this.title.textContent = productData.name || "";
    if (this.sku) this.sku.textContent = productData.sku ? `SKU: ${productData.sku}` : "";

    const desc = productData.shortDescription || productData.description || "";
    if (this.shortDescription) this.shortDescription.textContent = desc;

    this.show();
  }

  renderImage(image) {
    if (!this.imgContainer) return;
    this.imgContainer.innerHTML = "";
    console.log(image);
    if (!image) {
      this.renderFallbackImage(this.imgContainer);
      return;
    }

    if (typeof image === "string" && image.trim().startsWith("<svg")) {
      this.imgContainer.innerHTML = image;
      return;
    }

    if (typeof image === "string" && image.endsWith(".svg")) {
      fetch(image)
        .then((res) => res.text())
        .then((svg) => {
          // Safety: Only inject if the card is still supposed to be visible
          if (this.productCard.classList.contains("is-visible")) {
            this.imgContainer.innerHTML = svg;
          }
        })
        .catch(() => this.renderFallbackImage(this.imgContainer));
      return;
    }

    const img = document.createElement("img");
    img.className = "image";
    img.src = image;
    img.alt = this.title?.textContent || "Product Image";
    this.imgContainer.appendChild(img);
  }

  renderFallbackImage(container) {
    container.innerHTML = `
      <svg class="icon placeholder" aria-hidden="true">
        <use href="/public/assets/img/icons-sprite.svg#icon-image"></use>
      </svg>
    `;
  }

  show() {
    this.productCard?.classList.add("is-visible");
  }
  hide() {
    this.productCard?.classList.remove("is-visible");
  }

  clear() {
    if (this.title) this.title.textContent = "";
    if (this.sku) this.sku.textContent = "";
    if (this.shortDescription) this.shortDescription.textContent = "";
    if (this.imgContainer) this.imgContainer.innerHTML = "";
    this.hide();
  }

  destroy() {
    if (this.removeBtn) {
      this.removeBtn.removeEventListener("click", this.handleRemove);
    }
    this.clear();
    logger.debug("ProductCard destroyed");
  }
}
