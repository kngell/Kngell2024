// BrowserLogger.js - ULTRA SIMPLE VERSION
import DEBUG_CONFIG from "js/config/debug-config";

class BrowserLogger {
  constructor(pluginName, options = {}) {
    this.pluginName = pluginName;
    this.options = {
      level: options.level || "info",
      colors: options.colors !== false,
      emojis: options.emojis !== false,
      ...options,
    };
  }

  _getLevelNumber(level) {
    const levels = { error: 0, warn: 1, info: 2, debug: 3, trace: 4 };
    return levels[level] || 2;
  }

  _shouldLog(level) {
    // 1. Check for HARD BLOCK/TOGGLE
    const isDebugLevel = level === "debug" || level === "trace";

    // ⚡ MODIFIED LOGIC: If DEBUG is 0 AND the log is 'debug' or 'trace', block it.
    if (DEBUG_CONFIG && DEBUG_CONFIG.DEBUG === 0 && isDebugLevel) {
      return false; // Block debug/trace logs when DEBUG=0
    }

    // 2. Normal level checking for all logs (including info, warn, error, and also debug/trace when DEBUG=1)
    // This ensures the logger instance's set level is respected.
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
      trace: "🔍",
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

  // Log methods (unchanged)
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
