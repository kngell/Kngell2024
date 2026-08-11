import BaseFormModal from "../BaseFormModal";

export const MODAL_CONFIGS = {
  column: {
    modalIdentifier: "footer-column",
    formId: "footer-column-frm",
    triggerSelector: '[data-action="add-column"], [data-action="edit-column"]',
    entityEventName: "entity:saved",
    entityType: "column",
    reloadOnSuccess: false,
    responseKey: "footerMenuColumn"
  },
  link: {
    modalIdentifier: "footer-link",
    formId: "footer-link-frm",
    triggerSelector: '[data-action="add-link"], [data-action="edit-link"]',
    entityEventName: "entity:saved",
    entityType: "link",
    reloadOnSuccess: false,
    responseKey: "footerMenuLink"
  },
  about: {
    modalIdentifier: "footer-about",
    formId: "footer-about-frm-id",
    triggerSelector: '[data-action="add-about"], [data-action="edit-about"]',
    entityEventName: "entity:saved",
    entityType: "about",
    reloadOnSuccess: false,
    responseKey: "footerAbout"
  },
  social: {
    modalIdentifier: "footer-social",
    formId: "footer-social-frm",
    triggerSelector: '[data-action="add-social"], [data-action="edit-social"]',
    entityEventName: "entity:saved",
    entityType: "social",
    reloadOnSuccess: false,
    responseKey: "footerSocialMedia"
  }
};

export default class FooterModal extends BaseFormModal {
  constructor(type, options = {}) {
    const config = MODAL_CONFIGS[type];
    if (!config) {
      throw new Error(
        `Unknown footer modal type: ${type}. Available types: ${Object.keys(MODAL_CONFIGS).join(", ")}`
      );
    }

    // Extract callbacks
    const { onSuccess, onError, ...restOptions } = options;

    super(`Footer${capitalize(type)}Modal`, {
      ...restOptions,
      modalDataAttr: "modal",
      modalIdentifier: config.modalIdentifier,
      formId: config.formId,
      triggerSelector: config.triggerSelector,
      closeOnSuccess: true,
      reloadOnSuccess: config.reloadOnSuccess,
      submitButtonSelector: `button[form="${config.formId}"]`,
      entityEventName: config.entityEventName,
      autoBindTriggers: false,
      lazyInit: true,
      cacheModalContent: true,
      onSuccess: onSuccess || null,
      onError: onError || null,
      enableRedirectProcessor: false
    });

    this.type = type;
    this.entityType = config.entityType;
    this.responseKey = config.responseKey;
    this._modalContentCache = null;
    this._isInitialized = false;
    this._openPromise = null;
  }

  async initialize() {
    if (this._isInitialized) return;

    super.init();

    this._isInitialized = true;
    this.logger.debug(`Initializing ${this.type} modal`);

    await this._preWarmResources();
  }

  async _preWarmResources() {
    try {
      // Pre-warm if needed
    } catch (error) {
      this.logger.debug("Pre-warm failed:", error);
    }
  }

  async openModal(trigger) {
    await this.initialize();

    if (this.isRequesting) {
      this.logger.debug(`Modal ${this.type} is already loading`);
      return this._openPromise;
    }

    // ✅ Get the form that contains this trigger
    const form = trigger?.closest("form");
    if (!form) {
      this.logger.error(`No form found for ${this.type} modal`);
      return;
    }

    // ✅ Build modal URL from the form
    const formAction = form.getAttribute("action") || "";
    const idInput = form.querySelector('input[name="id"]');
    const id = idInput ? idInput.value : "";

    // Build the full URL
    const url = new URL(formAction, window.location.origin);
    if (id) {
      url.searchParams.set("id", id);
    }

    const modalUrl = url.toString();
    this.logger.debug(`Opening ${this.type} modal with URL: ${modalUrl}`);

    this._openPromise = this._performOpenModal(modalUrl);

    try {
      await this._openPromise;
    } finally {
      this._openPromise = null;
    }
  }

  async _performOpenModal(modalUrl) {
    this.isRequesting = true;

    try {
      this.logger.debug(`Fetching ${this.type} modal from: ${modalUrl}`);

      let result = this._getCachedModalContent(modalUrl);

      if (!result) {
        result = await this._fetchModalContent(modalUrl);
        this._cacheModalContent(modalUrl, result);
      }

      if (result.success === false) {
        this.logger.warn(`Failed to load ${this.type} modal:`, result.error);
        this._handleError(result.error);
        return;
      }

      const modalHtml = this._extractModalHtml(result);
      if (!modalHtml) {
        throw new Error(`Server did not return modal HTML for ${this.type}`);
      }

      this.showModal(modalHtml);
      this.initializeModalComponents();

      if (this.onModalOpened) {
        this.onModalOpened(this.currentModal);
      }

      this._trackModalOpen();
    } catch (error) {
      this.logger.error(`Failed to open ${this.type} modal:`, error);
      this._handleError(error.message);
    } finally {
      this.isRequesting = false;
    }
  }

  async _fetchModalContent(modalUrl, retries = 2) {
    const timeout = 10000;
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);

    try {
      const result = await this.ajax.get(modalUrl, null, {
        json: true,
        signal: controller.signal
      });
      clearTimeout(timeoutId);
      return result;
    } catch (error) {
      clearTimeout(timeoutId);

      if (error.name === "AbortError") {
        throw new Error(`Request timeout for ${this.type} modal`);
      }

      if (retries > 0) {
        this.logger.warn(`Retrying ${this.type} modal fetch... (${retries} attempts left)`);
        await new Promise((resolve) => setTimeout(resolve, 1000));
        return this._fetchModalContent(modalUrl, retries - 1);
      }

      throw error;
    }
  }

  _extractModalHtml(result) {
    if (this.responseKey && result[this.responseKey]) {
      return result[this.responseKey];
    }

    const patterns = [
      `footerMenu${capitalize(this.type)}`,
      `${this.type}ModalHtml`,
      `${this.type}Html`,
      "modalHtml",
      "html"
    ];

    for (const pattern of patterns) {
      if (result[pattern]) {
        return result[pattern];
      }
    }

    if (typeof result === "string") {
      return result;
    }

    return null;
  }

  _getCachedModalContent(url) {
    if (!this.options.cacheModalContent) return null;

    const cacheKey = this._getCacheKey(url);
    const cached = this._modalContentCache?.[cacheKey];

    if (cached && Date.now() - cached.timestamp < 300000) {
      this.logger.debug(`Using cached content for ${this.type}`);
      return cached.data;
    }

    return null;
  }

  _cacheModalContent(url, data) {
    if (!this.options.cacheModalContent) return;

    const cacheKey = this._getCacheKey(url);
    if (!this._modalContentCache) {
      this._modalContentCache = {};
    }

    this._modalContentCache[cacheKey] = {
      data: data,
      timestamp: Date.now()
    };
  }

  _getCacheKey(url) {
    return url.split("?")[0];
  }

  clearCache() {
    this._modalContentCache = null;
    this.logger.debug(`Cache cleared for ${this.type} modal`);
  }

  _handleError(message) {
    document.dispatchEvent(
      new CustomEvent("entity:save-error", {
        detail: {
          error: {
            message: `Failed to load ${this.type} form: ${message}`,
            original: { error: message, modalType: this.type }
          },
          operation: "load",
          context: { modalType: this.type }
        }
      })
    );

    this.logger.error(`Modal error [${this.type}]:`, message);
  }
  async openModalWithUrl(url) {
    await this.initialize();

    if (this.isRequesting) {
      this.logger.debug(`Modal ${this.type} is already loading`);
      return this._openPromise;
    }

    this.logger.debug(`Opening ${this.type} modal with URL: ${url}`);
    this._openPromise = this._performOpenModal(url);

    try {
      await this._openPromise;
    } finally {
      this._openPromise = null;
    }
  }
  async openModalWithForm(url, formData) {
    await this.initialize();

    if (this.isRequesting) {
      this.logger.debug(`Modal ${this.type} is already loading`);
      return this._openPromise;
    }

    this.logger.debug(`Opening ${this.type} modal with form data to: ${url}`);
    this._openPromise = this._performOpenModalWithForm(url, formData);

    try {
      await this._openPromise;
    } finally {
      this._openPromise = null;
    }
  }

  async _performOpenModalWithForm(url, formData) {
    this.isRequesting = true;

    try {
      this.logger.debug(`Submitting form to: ${url}`);

      const result = await this.ajax.post(url, formData, {
        json: true
      });

      if (result.success === false) {
        this.logger.warn(`Failed to load ${this.type} modal:`, result.error);
        this._handleError(result.error);
        return;
      }

      const modalHtml = this._extractModalHtml(result);
      if (!modalHtml) {
        throw new Error(`Server did not return modal HTML for ${this.type}`);
      }

      this.showModal(modalHtml);
      this.initializeModalComponents();

      if (this.onModalOpened) {
        this.onModalOpened(this.currentModal);
      }

      this._trackModalOpen();
    } catch (error) {
      this.logger.error(`Failed to open ${this.type} modal:`, error);
      this._handleError(error.message);
    } finally {
      this.isRequesting = false;
    }
  }
  _trackModalOpen() {
    try {
      if (window.gtag) {
        window.gtag("event", "modal_open", {
          modal_type: this.type,
          modal_name: `Footer ${capitalize(this.type)}`
        });
      }
    } catch (error) {
      // Silently fail for analytics
    }
  }

  getCustomProcessors() {
    const baseProcessors = super.getCustomProcessors ? super.getCustomProcessors() : [];

    return [
      ...baseProcessors,
      {
        handle: (context) => {
          const { result } = context;
          if (result?.success === true && result?.data) {
            try {
              document.dispatchEvent(
                new CustomEvent(this.entityEventName, {
                  detail: {
                    [this.entityType]: result.data,
                    result: result,
                    modalType: this.type
                  },
                  bubbles: true
                })
              );
            } catch (error) {
              this.logger.error(`Failed to dispatch ${this.entityEventName}:`, error);
            }
          }
        }
      }
    ];
  }

  processFormData(data, formEl) {
    return {
      ...data,
      _modalType: this.type,
      _entityType: this.entityType
    };
  }

  destroy() {
    this._modalContentCache = null;
    this._isInitialized = false;
    this._openPromise = null;
    super.destroy();
  }
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}
