const DEBUG = 1;
class BrowserLogger {
  static instances = []; // keep track of all created loggers
  constructor(pluginName, options = {}) {
    this.pluginName = pluginName;
    this.options = {
      level: options.level || this._getLogLevel(),
      colors: options.colors !== false,
      emojis: options.emojis !== false,
      ...options,
    };

    // Add to global registry
    BrowserLogger.instances.push(this);

    this._log("debug", `Logger initialized`, {
      level: this.options.level,
      hostname: window.location.hostname,
      isLocalDevelopment: this._isLocalDevelopment(),
    });
  }

  _isLocalDevelopment() {
    const hostname = window.location.hostname;
    return (
      hostname === "localhost" ||
      hostname.startsWith("192.168.") ||
      hostname.startsWith("127.0.0.") ||
      hostname.endsWith(".local") ||
      hostname === "[::1]" ||
      window.location.host.includes("localhost") || // This catches localhost:3003
      window.location.host.includes("127.0.0.1")
    );
  }

  _getLogLevel() {
    // 1. Auto-enable debug in local development (your great idea!)
    if (this._isLocalDevelopment() && DEBUG == 1) {
      return "debug";
    }
    if (this._isLocalDevelopment() && DEBUG == 0) {
      return "info";
    }

    // 2. Check for webpack-injected environment variables (if using DefinePlugin)
    if (typeof process !== "undefined" && process.env) {
      if (process.env.LOG_LEVEL) {
        return process.env.LOG_LEVEL;
      }
      if (process.env.DEBUG === "1") {
        return "debug";
      }
    }

    // 3. Check URL parameters (for temporary override)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("debug")) return "debug";
    if (urlParams.has("log")) return urlParams.get("log");

    // 4. Check global config (for manual override via HTML)
    if (window.APP_CONFIG?.LOG_LEVEL) return window.APP_CONFIG.LOG_LEVEL;

    // 5. Default to info for production
    return "info";
  }

  _getLevelNumber(level) {
    const levels = { error: 0, warn: 1, info: 2, debug: 3, trace: 4 };
    return levels[level] || 2;
  }

  _shouldLog(level) {
    return this._getLevelNumber(level) <= this._getLevelNumber(this.options.level);
  }
  setDebug(enabled) {
    if (this._isLocalDevelopment()) {
      const newLevel = enabled ? "debug" : "info";
      // Update this logger + all other registered ones
      BrowserLogger.instances.forEach((logger) => {
        logger.options.level = newLevel;
      });

      this._log("info", `🔧 Debug mode ${enabled ? "enabled" : "disabled"} globally`);
    } else {
      this._log("warn", "setDebug ignored in production");
    }
  }

  child(subName) {
    return new BrowserLogger(`${this.pluginName}:${subName}`, this.options);
  }

  _log(level, message, data = null) {
    if (!this._shouldLog(level)) return;

    const emojis = {
      info: "ℹ️",
      success: "✅",
      warn: "⚠️",
      error: "❌",
      debug: "🐞",
      trace: "🔍",
    };

    const colors = {
      info: "color: #3498db;",
      success: "color: #27ae60; font-weight: bold;",
      warn: "color: #f39c12;",
      error: "color: #e74c3c;",
      debug: "color: #95a5a6;",
      trace: "color: #9b59b6;",
    };

    const timestamp = new Date().toLocaleTimeString();
    const prefix = `[${timestamp}] [${this.pluginName}]`;
    const emoji = this.options.emojis ? `${emojis[level] || ""} ` : "";

    const fullMessage = `${prefix} ${emoji}${message}`;

    switch (level) {
      case "trace":
        console.trace(fullMessage, data || "");
        break;
      case "debug":
        console.debug(fullMessage, data || "");
        break;
      case "info":
        console.info(fullMessage);
        break;
      case "success":
        console.log(`%c${fullMessage}`, colors.success);
        break;
      case "warn":
        console.warn(fullMessage);
        break;
      case "error":
        console.error(fullMessage);
        if (data) console.error(data);
        break;
      default:
        console.log(fullMessage, data || "");
    }
  }

  trace(message, data = null) {
    this._log("trace", message, data);
  }

  debug(message, data = null) {
    this._log("debug", message, data);
  }

  info(message) {
    this._log("info", message);
  }

  success(message) {
    this._log("success", message);
  }

  warn(message) {
    this._log("warn", message);
  }

  error(message, error = null) {
    this._log("error", message, error);
  }

  child(subName) {
    return new BrowserLogger(`${this.pluginName}:${subName}`, this.options);
  }

  // Utility method to check current log level
  getCurrentLevel() {
    return this.options.level;
  }

  // Utility method to check if debug is enabled
  isDebugEnabled() {
    return this._shouldLog("debug");
  }
}

// Create and export default instance
const logger = new BrowserLogger("System");

export { BrowserLogger };
export default logger;
