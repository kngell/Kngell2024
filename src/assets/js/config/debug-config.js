const DEBUG_CONFIG = {
  DEBUG: 1,

  PERSISTENT_LOGGING: {
    ENABLED: true,
    STORAGE_TYPE: "sessionStorage",
    MAX_ENTRIES: 1000,
    PERSIST_LEVEL: "debug",
    AUTO_SHOW_ON_ERROR: true
  },

  MODULES: {
    FORM_VALIDATOR: true,
    AJAX_HANDLER: true,
    REAL_TIME_VALIDATOR: true,
    PRODUCT_DELETION_MODAL: true
  }
};

export default DEBUG_CONFIG;
