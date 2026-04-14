import BrowserLogger from "js/core/utils/logger";

const logger = new BrowserLogger("ProductVariationManager");

export default class ProductVariationManager {
  constructor(container) {
    this.container = container;
    this.variations = [];
    this.isInitialized = false;

    // Track variation state
    this.variationCounter = 0;
    this.variationTemplates = new Map();

    // Bind methods
    this.handleAddVariation = this.handleAddVariation.bind(this);
    this.handleRemoveVariation = this.handleRemoveVariation.bind(this);
  }

  async initialize() {
    if (this.isInitialized) return;

    try {
      logger.info("Initializing ProductVariationManager");

      // Load existing variations from DOM
      this.loadExistingVariations();

      // Setup event listeners
      this.setupEventListeners();

      // Cache templates
      this.cacheTemplates();

      this.isInitialized = true;
      logger.success("ProductVariationManager initialized");
    } catch (error) {
      logger.error("Failed to initialize ProductVariationManager:", error);
      throw error;
    }
  }

  loadExistingVariations() {
    const variationElements = this.container.querySelectorAll("[data-variation-item]");
    logger.debug(`Found ${variationElements.length} existing variations`);

    this.variations = Array.from(variationElements).map((element, index) => {
      const variation = this.extractVariationData(element, index);
      return {
        element,
        data: variation,
        index,
      };
    });

    // Update counter
    this.variationCounter = this.variations.length;
  }

  extractVariationData(element, index) {
    const data = {
      index,
      attributes: [],
    };

    // Extract variation fields
    const fields = element.querySelectorAll('[name^="variations["]');
    fields.forEach((field) => {
      const match = field.name.match(/variations\[(\d+)\]\[([^\[\]]+)\]/);
      if (match) {
        const [, varIndex, fieldName] = match;
        if (parseInt(varIndex) === index) {
          data[fieldName] = this.parseFieldValue(field);
        }
      }

      // Extract attributes
      const attrMatch = field.name.match(/variations\[(\d+)\]\[attributes\]\[(\d+)\]\[([^\]]+)\]/);
      if (attrMatch) {
        const [, varIndex, attrIndex, attrField] = attrMatch;
        if (parseInt(varIndex) === index) {
          if (!data.attributes[attrIndex]) {
            data.attributes[attrIndex] = {};
          }
          data.attributes[attrIndex][attrField] = this.parseFieldValue(field);
        }
      }
    });

    // Clean up attributes
    if (data.attributes) {
      data.attributes = data.attributes.filter(
        (attr) => attr && (attr.attribute_name || attr.attribute_value || attr.id),
      );
    }

    return data;
  }

  parseFieldValue(field) {
    if (field.type === "checkbox") {
      return field.checked;
    }
    if (field.type === "number") {
      return field.value === "" ? null : Number(field.value);
    }
    if (field.type === "select-one") {
      return field.value;
    }
    return field.value;
  }

  cacheTemplates() {
    // Cache variation template
    const template = this.container.querySelector("[data-variation-template]");
    if (template) {
      this.variationTemplate = template.innerHTML;
      template.remove();
    }

    // Cache attribute template if exists
    const attrTemplate = this.container.querySelector("[data-attribute-template]");
    if (attrTemplate) {
      this.attributeTemplate = attrTemplate.innerHTML;
      attrTemplate.remove();
    }

    logger.debug("Templates cached");
  }

  setupEventListeners() {
    // Add variation button
    const addButton = this.container.querySelector("[data-add-variation]");
    if (addButton) {
      addButton.addEventListener("click", this.handleAddVariation);
    }

    // Remove variation buttons
    this.container.addEventListener("click", (e) => {
      const removeBtn = e.target.closest("[data-remove-variation]");
      if (removeBtn) {
        const variationElement = removeBtn.closest("[data-variation-item]");
        if (variationElement) {
          this.handleRemoveVariation(variationElement);
        }
      }
    });

    // Variation type change
    this.container.addEventListener("change", (e) => {
      if (e.target.name && e.target.name.includes("variation_type_id")) {
        this.handleVariationTypeChange(e.target);
      }
    });
  }

  handleAddVariation() {
    const newIndex = this.variationCounter++;
    const variationHtml = this.generateVariationHtml(newIndex);

    const variationElement = this.createDOMElement(variationHtml);
    variationElement.setAttribute("data-variation-item", "true");

    // Insert before add button
    const addButton = this.container.querySelector("[data-add-variation]");
    if (addButton) {
      addButton.parentNode.insertBefore(variationElement, addButton);
    } else {
      this.container.appendChild(variationElement);
    }

    // Store reference
    this.variations.push({
      element: variationElement,
      data: this.extractVariationData(variationElement, newIndex),
      index: newIndex,
    });

    logger.debug(`Added variation #${newIndex}`);

    // Dispatch event
    const event = new CustomEvent("variation:added", {
      detail: { index: newIndex, element: variationElement },
    });
    this.container.dispatchEvent(event);
  }

  handleRemoveVariation(variationElement) {
    const index = this.variations.findIndex((v) => v.element === variationElement);
    if (index === -1) return;

    // Remove from DOM
    variationElement.remove();

    // Remove from array
    this.variations.splice(index, 1);

    // Reindex remaining variations
    this.reindexVariations();

    logger.debug(`Removed variation #${index}`);

    // Dispatch event
    const event = new CustomEvent("variation:removed", {
      detail: { index },
    });
    this.container.dispatchEvent(event);
  }

  reindexVariations() {
    this.variations.forEach((variation, index) => {
      variation.index = index;
      variation.data.index = index;

      // Update DOM element indices
      this.updateVariationIndex(variation.element, index);
    });

    // Update counter
    this.variationCounter = this.variations.length;
  }

  updateVariationIndex(element, newIndex) {
    // Update all field names with new index
    const fields = element.querySelectorAll('[name^="variations["]');
    fields.forEach((field) => {
      const oldName = field.name;
      const newName = oldName.replace(/variations\[\d+\]/, `variations[${newIndex}]`);
      field.name = newName;
      field.id = field.id?.replace(/variations\[\d+\]/, `variations[${newIndex}]`);
    });
  }

  handleVariationTypeChange(field) {
    const match = field.name.match(/variations\[(\d+)\]/);
    if (!match) return;

    const variationIndex = parseInt(match[1]);
    const variationTypeId = field.value;

    logger.debug(`Variation type changed for #${variationIndex}: ${variationTypeId}`);

    // Dispatch event
    const event = new CustomEvent("variation:type-changed", {
      detail: { variationIndex, variationTypeId },
    });
    this.container.dispatchEvent(event);
  }

  generateVariationHtml(index) {
    if (!this.variationTemplate) {
      logger.error("No variation template found");
      return "";
    }

    // Replace placeholder with actual index
    let html = this.variationTemplate
      .replace(/{{index}}/g, index)
      .replace(/variations\[0\]/g, `variations[${index}]`);

    return html;
  }

  createDOMElement(html) {
    const template = document.createElement("template");
    template.innerHTML = html.trim();
    return template.content.firstChild;
  }

  // ============ PUBLIC API ============

  getData() {
    return this.variations.map((v) => v.data);
  }

  validate() {
    const errors = [];

    this.variations.forEach((variation, index) => {
      // Check required fields
      const requiredFields = variation.element.querySelectorAll("[required]");
      requiredFields.forEach((field) => {
        if (!field.value && field.value !== "0") {
          errors.push({
            field: field.name,
            message: `${field.labels?.[0]?.textContent || field.name} is required`,
            variationIndex: index,
          });
        }
      });

      // Check SKU uniqueness
      const skuField = variation.element.querySelector('[name$="[sku]"]');
      if (skuField && skuField.value) {
        const duplicate = this.variations.find(
          (v, i) =>
            i !== index && v.element.querySelector('[name$="[sku]"]')?.value === skuField.value,
        );
        if (duplicate) {
          errors.push({
            field: skuField.name,
            message: "SKU must be unique across variations",
            variationIndex: index,
          });
        }
      }
    });

    return {
      isValid: errors.length === 0,
      errors,
      variationCount: this.variations.length,
    };
  }

  getVariation(index) {
    return this.variations[index];
  }

  getAllVariations() {
    return this.variations;
  }

  getVariationCount() {
    return this.variations.length;
  }

  addAttribute(variationIndex, attributeData) {
    const variation = this.getVariation(variationIndex);
    if (!variation) {
      logger.error(`Variation ${variationIndex} not found`);
      return false;
    }

    // Implementation for adding attributes dynamically
    // This would require an attribute template
    logger.debug(`Adding attribute to variation ${variationIndex}`, attributeData);
    return true;
  }

  reset() {
    // Remove all but the first variation
    while (this.variations.length > 1) {
      this.handleRemoveVariation(this.variations[this.variations.length - 1].element);
    }

    // Reset first variation
    if (this.variations.length > 0) {
      const firstVariation = this.variations[0];
      const inputs = firstVariation.element.querySelectorAll("input, select, textarea");
      inputs.forEach((input) => {
        if (input.type !== "hidden") {
          input.value = "";
          if (input.type === "checkbox") {
            input.checked = false;
          }
        }
      });
    }

    logger.debug("Variations reset");
  }

  destroy() {
    // Clean up event listeners
    const addButton = this.container.querySelector("[data-add-variation]");
    if (addButton) {
      addButton.removeEventListener("click", this.handleAddVariation);
    }

    this.container.removeEventListener("click", (e) => {
      const removeBtn = e.target.closest("[data-remove-variation]");
      if (removeBtn) {
        const variationElement = removeBtn.closest("[data-variation-item]");
        if (variationElement) {
          this.handleRemoveVariation(variationElement);
        }
      }
    });

    this.container.removeEventListener("change", (e) => {
      if (e.target.name && e.target.name.includes("variation_type_id")) {
        this.handleVariationTypeChange(e.target);
      }
    });

    // Clear arrays
    this.variations = [];
    this.variationTemplates.clear();

    this.isInitialized = false;
    logger.debug("ProductVariationManager destroyed");
  }

  getStatus() {
    return {
      isInitialized: this.isInitialized,
      variationCount: this.variations.length,
      variationCounter: this.variationCounter,
      hasTemplate: !!this.variationTemplate,
    };
  }
}
