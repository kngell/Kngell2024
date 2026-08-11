import BrowserLogger from "js/core/utils/logger";

const logger = new BrowserLogger("ValidationService");

export default class ValidationService {
  constructor() {
    this.cache = new Map();
    this.loadingPromises = new Map();
    this.warningsShown = new Set();
    this.baseUrl = this.getApiBaseUrl();
  }

  static getInstance() {
    if (!this._instance) {
      this._instance = new ValidationService();
    }
    return this._instance;
  }

  async load(rulesName, forceRefresh = false) {
    if (!rulesName) {
      rulesName = this.getRulesFileFromDOM();
    }

    // Return cached if available and not forcing refresh
    if (!forceRefresh && this.cache.has(rulesName)) {
      return this.cache.get(rulesName);
    }

    // Return existing loading promise if already loading
    if (this.loadingPromises.has(rulesName)) {
      return this.loadingPromises.get(rulesName);
    }

    // Load new rules
    const loadingPromise = this.fetchRules(rulesName);
    this.loadingPromises.set(rulesName, loadingPromise);

    try {
      const data = await loadingPromise;

      if (data.error) {
        throw new Error(data.error);
      }

      const rulesData = {
        rules: data.rules,
        settings: data.settings,
        loadedAt: Date.now()
      };

      this.cache.set(rulesName, rulesData);
      return rulesData;
    } catch (error) {
      // Show warning only once
      if (!this.warningsShown.has(rulesName)) {
        this.showValidationWarning(rulesName, error.message);
        this.warningsShown.add(rulesName);
      }

      // Return fallback rules
      return this.getFallbackRules(rulesName);
    } finally {
      this.loadingPromises.delete(rulesName);
    }
  }

  async fetchRules(rulesName) {
    const timestamp = process.env.NODE_ENV === "development" ? `&t=${Date.now()}` : "";
    const url = `${this.baseUrl}/get-settings?rules=${rulesName}${timestamp}`;
    const response = await fetch(url);

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: Failed to load ${rulesName}`);
    }

    return response.json();
  }

  getApiBaseUrl() {
    return process.env.NODE_ENV === "development" ? "/api/form-validation" : "/api/validation-api";
  }

  getRulesFileFromDOM() {
    const form = document.querySelector('form[data-validate="true"]');
    return form?.dataset.validationRules || "productRules";
  }

  getFallbackRules(rulesName) {
    const fallbacks = {
      productRules: {
        rules: {
          sku: { display: "SKU", required: true, min: 3, max: 64 },
          name: { display: "Product Name", required: true, min: 3, max: 255 }
        },
        settings: {
          messages: {
            required: "%s is required.",
            min: "%s must be at least %s characters.",
            max: "%s must be at most %s characters."
          }
        }
      },
      product_deletion: {
        rules: {
          confirm_delete: {
            display: "Delete Confirmation",
            required: true,
            required_checked: true
          },
          confirm_irreversible: {
            display: "Irreversible Action",
            required: true,
            required_checked: true
          }
        },
        settings: {
          messages: {
            required: "%s is required.",
            required_checked: "You must check %s to proceed."
          }
        }
      }
    };

    return (
      fallbacks[rulesName] || {
        rules: {},
        settings: { messages: { required: "%s is required." } }
      }
    );
  }

  showValidationWarning(rulesName, message) {
    const warningElement = document.createElement("div");
    warningElement.className = "validation-warning";
    warningElement.style.cssText = `
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      color: #856404;
      padding: 12px;
      margin: 10px 0;
      border-radius: 4px;
      font-size: 14px;
    `;
    warningElement.innerHTML = `
      <strong>Note:</strong> ${rulesName} validation is temporarily unavailable. 
      Your form will still be validated when submitted.
      <br><small>${message}</small>
    `;

    const form = document.querySelector("form[data-validate]");
    if (form) {
      form.parentNode.insertBefore(warningElement, form);
    }
  }

  // Optional utility methods (you can remove if not needed)
  clearCache(rulesName = null) {
    if (rulesName) {
      this.cache.delete(rulesName);
      this.warningsShown.delete(rulesName);
    } else {
      this.cache.clear();
      this.warningsShown.clear();
    }
  }

  getStatus() {
    return {
      cacheSize: this.cache.size,
      loadingCount: this.loadingPromises.size,
      warningsShown: Array.from(this.warningsShown)
    };
  }
}
