// ES6 Module Logger
const supportsColor = process.stdout.isTTY;

// Log level hierarchy
const LOG_LEVELS = {
  error: 0,
  warn: 1,
  info: 2,
  success: 2,
  debug: 3,
  trace: 4,
};

const COLORS = {
  reset: "\x1b[0m",
  bold: "\x1b[1m",
  dim: "\x1b[2m",
  gray: "\x1b[90m",
  cyan: "\x1b[36m",
  green: "\x1b[32m",
  yellow: "\x1b[33m",
  red: "\x1b[31m",
  magenta: "\x1b[35m",
};

const emojiPrefix = {
  info: "ℹ️",
  success: "✅",
  warn: "⚠️",
  error: "❌",
  debug: "🐞",
  trace: "🔍",
};

const colorMap = {
  info: COLORS.cyan,
  success: COLORS.green,
  warn: COLORS.yellow,
  error: COLORS.red,
  debug: COLORS.gray,
  trace: COLORS.magenta,
};

function colorize(color, text) {
  return supportsColor ? `${color}${text}${COLORS.reset}` : text;
}

function padPlugin(name) {
  const maxLen = 18;
  const trimmed = name.length > maxLen ? name.slice(0, maxLen - 1) + "…" : name;
  return `${COLORS.bold}[${trimmed}]`.padEnd(maxLen + 3);
}

class Logger {
  constructor(pluginName, logLevel = null) {
    this.pluginName = pluginName;
    this.logLevel = logLevel || this._getLogLevel();
  }

  _getLogLevel() {
    // Get log level from environment variables or use default
    if (typeof process !== "undefined" && process.env) {
      return process.env.LOG_LEVEL || (process.env.DEBUG === "1" ? "debug" : "info");
    }
    // Fallback for browser environment
    if (typeof window !== "undefined" && window.APP_CONFIG) {
      return window.APP_CONFIG.LOG_LEVEL || "info";
    }
    return "info";
  }

  _shouldLog(type) {
    return LOG_LEVELS[type] <= LOG_LEVELS[this.logLevel];
  }

  _log(type, message, data = null) {
    if (!this._shouldLog(type)) return;

    const emoji = emojiPrefix[type] || "";
    const color = colorMap[type] || "";
    const paddedName = padPlugin(this.pluginName);

    let output = `${paddedName} ${emoji} ${color}${message}${COLORS.reset}`;

    if (data && (type === "debug" || type === "trace")) {
      output += `\n${colorize(COLORS.dim, JSON.stringify(data, null, 2))}`;
    }

    console.log(output);
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
    this._log("error", message);
    if (error) {
      console.error(colorize(COLORS.red, error.stack || error.message));
    }
  }

  child(subName) {
    return new Logger(`${this.pluginName}:${subName}`, this.logLevel);
  }
}

// Create default instance
const defaultLogger = new Logger("System");

// Export both the class and default instance
export { Logger };
export default defaultLogger;
