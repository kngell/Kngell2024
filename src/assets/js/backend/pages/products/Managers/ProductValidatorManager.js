import BrowserLogger from "js/utils/logger";
import Validator from "js/core/validation/Validator";

const logger = new BrowserLogger("ProductValidatorManager");

export default class ProductValidatorManager {
  constructor() {
    this.validators = new Map();
    this.rules = null;
    this.settings = null;
    this.isInitialized = false;
  }

  async initialize() {
    if (this.isInitialized) {
      logger.warn("Product validator already initialized, skipping");
      return;
    }

    logger.debug("Starting product validator initialization");

    try {
      await this._loadValidationRules();
      this.isInitialized = true;

      // Optional: Run tests in development
      // if (process.env.NODE_ENV === "development") {
      //   this._runValidationTests();
      // }
    } catch (error) {
      logger.error("Failed to initialize product validator", error);
      this._showValidationWarning(error.message);
    }
  }

  async _loadValidationRules() {
    const rulesFile = this._getRulesFile();
    const baseUrl = this._getApiBaseUrl();
    const rulesUrl = `${baseUrl}/get-settings?rules=${rulesFile}`;
    const finalUrl =
      process.env.NODE_ENV === "development" ? `${rulesUrl}&debug=1&t=${Date.now()}` : rulesUrl;

    logger.debug(`Fetching product validation rules from: ${finalUrl}`);

    const response = await fetch(finalUrl);

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: Failed to load product rules`);
    }

    const data = await response.json();

    if (data.error) {
      throw new Error(data.error);
    }

    this.rules = data.rules;
    this.settings = data.settings;

    logger.success(`Loaded product validation rules for: ${rulesFile}`);
  }

  createValidator(form) {
    if (!this.isInitialized) {
      throw new Error("Validator manager not initialized");
    }

    const validator = new Validator(this.rules, {}, this.settings);
    this.validators.set(form, validator);
    return validator;
  }

  getValidator(form) {
    return this.validators.get(form);
  }

  removeValidator(form) {
    this.validators.delete(form);
  }

  _getRulesFile() {
    const form = document.querySelector('form[data-validate="true"]');
    return form?.dataset.validationRules || "productRules";
  }

  _getApiBaseUrl() {
    return process.env.NODE_ENV === "development" ? "/form-validation-api" : "/api/validation-api";
  }

  _showValidationWarning(message) {
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
      <strong>Note:</strong> Product validation is temporarily unavailable. 
      Your form will still be validated when submitted.
      <br><small>${message}</small>
    `;

    const form = document.querySelector("form[data-validate]");
    if (form) {
      form.parentNode.insertBefore(warningElement, form);
    }
  }
}
