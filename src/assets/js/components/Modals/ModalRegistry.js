// js/components/Modals/ModalRegistry.js

import BrowserLogger from "js/core/utils/BrowserLogger";
import FooterModal, { MODAL_CONFIGS } from "js/components/Modals/Concrete/FooterModal";
import DeletionModal from "js/components/Modals/Concrete/DeletionModal";
import AddressModal from "js/components/Modals/Concrete/AddressModal"; // ✅ Import

const logger = new BrowserLogger("ModalRegistry");

class ModalRegistry {
  constructor() {
    this._modals = new Map();
    this._loadingModals = new Map();
    this._config = {
      lazyLoad: true,
      cacheInstances: true,
      maxInstances: 5
    };
  }

  async getModal(type, options = {}) {
    const cacheKey = this._getCacheKey(type, options);
    logger.debug("Config: ", this._config);
    logger.debug("Config: ", options);

    if (this._config.cacheInstances && this._modals.has(cacheKey)) {
      const modal = this._modals.get(cacheKey);

      if (options.onSuccess) {
        modal.onSuccess = options.onSuccess;
      }
      if (options.onError) {
        modal.onError = options.onError;
      }

      logger.debug("🔍 ModalRegistry: Retrieved cached modal, onSuccess:", !!modal.onSuccess);

      if (modal.initialize && typeof modal.initialize === "function") {
        await modal.initialize();
      }
      return modal;
    } else {
      logger.debug("Bad cacheKey or Cache Instance");
    }

    // ✅ Check if already loading
    if (this._loadingModals.has(cacheKey)) {
      return this._loadingModals.get(cacheKey);
    }

    // ✅ Create new modal
    const loadPromise = this._createModal(type, options);
    this._loadingModals.set(cacheKey, loadPromise);

    try {
      const modal = await loadPromise;

      // Cache the modal
      if (this._config.cacheInstances) {
        this._modals.set(cacheKey, modal);
        this._enforceMaxInstances();
      }

      return modal;
    } finally {
      this._loadingModals.delete(cacheKey);
    }
  }

  async _createModal(type, options) {
    // ✅ Handle address modal type
    if (type === "address") {
      const modal = new AddressModal({
        ...options,
        autoBindTriggers: true
      });
      return modal;
    }

    if (type === "deletion") {
      const modal = new DeletionModal({
        ...options,
        autoBindTriggers: true
      });
      return modal;
    }

    const config = MODAL_CONFIGS[type];
    if (!config) {
      throw new Error(`Unknown modal type: ${type}`);
    }

    const modalOptions = {
      ...options,
      lazyInit: true,
      cacheModalContent: true,
      autoBindTriggers: false
    };

    const modal = new FooterModal(type, modalOptions);

    if (!this._config.lazyLoad) {
      await modal.initialize();
    }

    return modal;
  }

  _getCacheKey(type, options = {}) {
    const keyParts = [type];
    const significantOptions = ["modalIdentifier", "formId"];
    for (const opt of significantOptions) {
      if (options[opt]) {
        keyParts.push(`${opt}:${options[opt]}`);
      }
    }
    return keyParts.join("|");
  }

  _enforceMaxInstances() {
    if (this._modals.size > this._config.maxInstances) {
      const entries = Array.from(this._modals.entries());
      const toRemove = entries.slice(0, this._modals.size - this._config.maxInstances);

      for (const [key, modal] of toRemove) {
        modal.destroy?.();
        this._modals.delete(key);
      }
    }
  }

  async preload(types = ["column", "link", "social"]) {
    const promises = types.map((type) => this.getModal(type));
    await Promise.allSettled(promises);
  }

  getActiveModals() {
    return Array.from(this._modals.values());
  }

  clearCache() {
    for (const modal of this._modals.values()) {
      modal.clearCache?.();
    }
  }

  destroyAll() {
    for (const modal of this._modals.values()) {
      modal.destroy?.();
    }
    this._modals.clear();
    this._loadingModals.clear();
  }
}

let registryInstance = null;

export function getModalRegistry() {
  if (!registryInstance) {
    registryInstance = new ModalRegistry();
  }
  return registryInstance;
}

export default ModalRegistry;
