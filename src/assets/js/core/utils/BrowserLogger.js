import DEBUG_CONFIG from "js/config/debug-config";

class BrowserLogger {
  constructor(pluginName, options = {}) {
    this.pluginName = pluginName;
    this.options = {
      level: options.level || "info",
      colors: options.colors !== false,
      emojis: options.emojis !== false,
      ...options
    };
  }

  _getLevelNumber(level) {
    const levels = { error: 0, warn: 1, info: 2, debug: 3, trace: 4 };
    return levels[level] || 2;
  }

  _shouldLog(level) {
    const isDebugLevel = level === "debug" || level === "trace";

    // GLOBAL DEBUG CHECK - this overrides everything
    if (DEBUG_CONFIG && DEBUG_CONFIG.DEBUG === 1) {
      // If global debug is ON, log everything regardless of instance level
      return true;
    }

    // If global debug is OFF, block debug/trace logs
    if (DEBUG_CONFIG && DEBUG_CONFIG.DEBUG === 0 && isDebugLevel) {
      return false;
    }

    // Normal level checking for non-debug logs when global debug is off
    return this._getLevelNumber(level) <= this._getLevelNumber(this.options.level);
  }

  _log(level, message, data = null) {
    if (!this._shouldLog(level)) return;

    const emojis = {
      info: "ℹ️",
      success: "✅",
      warn: "⚠️",
      error: "❌",
      debug: "🐞",
      trace: "🔍"
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
        data ? console.log(fullMessage, data) : console.log(fullMessage);
        break;
      case "info":
        console.info(fullMessage);
        break;
      case "success":
        console.log(`%c${fullMessage}`, "color: #27ae60; font-weight: bold;");
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
}

export default BrowserLogger;
