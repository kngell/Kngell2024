import BrowserLogger from "./BrowserLogger";
import DEBUG_CONFIG from "js/config/debug-config";

// 1. Determine the global log level based on the configuration value
const GLOBAL_LOG_LEVEL = DEBUG_CONFIG.DEBUG === 1 ? "debug" : "info";

// 2. Create a Wrapper Class that enforces the global log level on instantiation
class Logger extends BrowserLogger {
  constructor(pluginName, options = {}) {
    // Force the level option to the one derived from DEBUG_CONFIG
    const mergedOptions = {
      ...options,
      level: GLOBAL_LOG_LEVEL,
    };

    // Pass the plugin name and the forced level to the parent constructor
    super(pluginName, mergedOptions);
  }
}

// 3. Export the wrapper class
export default Logger;
