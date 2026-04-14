import DEBUG_CONFIG from "js/config/debug-config";
import BrowserLogger from "./BrowserLogger";

export default class PersistentLogger {
  constructor(pluginName, options = {}) {
    this.pluginName = pluginName;
    this.consoleLogger = new BrowserLogger(pluginName, options);

    // Storage key
    this.storageKey = `logs_${pluginName}`;

    // Options
    this.options = {
      maxEntries: options.maxEntries || 1000,
      storageType: options.storageType || "sessionStorage", // 'localStorage' or 'sessionStorage'
      persistLevel: options.persistLevel || "warn", // Only persist warnings and above by default
      autoShowOnError: options.autoShowOnError !== false,
      ...options,
    };

    // Initialize storage
    this._initStorage();

    // Auto-show logs if there are errors from previous session
    if (this.options.autoShowOnError && DEBUG_CONFIG.DEBUG === 1) {
      this._checkForErrors();
    }
  }

  _initStorage() {
    // Only initialize if DEBUG is enabled
    if (DEBUG_CONFIG.DEBUG !== 1) return;

    try {
      const storage = this._getStorage();
      const existing = storage.getItem(this.storageKey);
      if (!existing) {
        storage.setItem(this.storageKey, JSON.stringify([]));
      }
    } catch (e) {
      this.consoleLogger.error("Failed to initialize persistent storage:", e);
    }
  }

  _getStorage() {
    return this.options.storageType === "localStorage" ? localStorage : sessionStorage;
  }

  _shouldPersist(level) {
    const persistLevels = {
      trace: 0,
      debug: 1,
      info: 2,
      warn: 3,
      error: 4,
    };

    const currentLevel = persistLevels[level] || 2;
    const minLevel = persistLevels[this.options.persistLevel] || 2;

    return currentLevel >= minLevel;
  }

  _saveLog(level, message, data = null) {
    // Only save if DEBUG is enabled
    if (DEBUG_CONFIG.DEBUG !== 1) return;

    try {
      const storage = this._getStorage();
      const logs = this.getLogs();

      const entry = {
        timestamp: new Date().toISOString(),
        plugin: this.pluginName,
        level,
        message,
        data: this._safeStringify(data),
        url: window.location.href,
        userAgent: navigator.userAgent,
      };

      logs.push(entry);

      // Limit entries
      if (logs.length > this.options.maxEntries) {
        logs.splice(0, logs.length - this.options.maxEntries);
      }

      storage.setItem(this.storageKey, JSON.stringify(logs));
    } catch (e) {
      // Silent fail - don't break the app
    }
  }

  _safeStringify(data) {
    if (data === undefined) return "undefined";
    if (data === null) return "null";

    try {
      if (typeof data === "object") {
        // Handle circular references
        const cache = new Set();
        return JSON.stringify(
          data,
          (key, value) => {
            if (typeof value === "object" && value !== null) {
              if (cache.has(value)) return "[Circular]";
              cache.add(value);
            }
            return value;
          },
          2,
        );
      }
      return String(data);
    } catch (e) {
      return `[Stringify Error: ${e.message}]`;
    }
  }

  // Public logging methods that mirror BrowserLogger
  trace(message, data = null) {
    this.consoleLogger.trace(message, data);
    if (this._shouldPersist("trace")) {
      this._saveLog("trace", message, data);
    }
  }

  debug(message, data = null) {
    this.consoleLogger.debug(message, data);
    if (this._shouldPersist("debug")) {
      this._saveLog("debug", message, data);
    }
  }

  info(message, data = null) {
    this.consoleLogger.info(message);
    if (this._shouldPersist("info")) {
      this._saveLog("info", message, data);
    }
  }

  success(message, data = null) {
    this.consoleLogger.success(message);
    if (this._shouldPersist("info")) {
      this._saveLog("info", `✅ ${message}`, data);
    }
  }

  warn(message, data = null) {
    this.consoleLogger.warn(message);
    if (this._shouldPersist("warn")) {
      this._saveLog("warn", message, data);
    }
  }

  error(message, error = null) {
    this.consoleLogger.error(message, error);
    if (this._shouldPersist("error")) {
      this._saveLog("error", message, error);
    }
  }

  // Persistent log specific methods
  getLogs() {
    try {
      const storage = this._getStorage();
      const logs = storage.getItem(this.storageKey);
      return logs ? JSON.parse(logs) : [];
    } catch (e) {
      return [];
    }
  }

  clearLogs() {
    try {
      const storage = this._getStorage();
      storage.removeItem(this.storageKey);
      this._initStorage();
      return true;
    } catch (e) {
      return false;
    }
  }

  getStats() {
    const logs = this.getLogs();
    const stats = {
      total: logs.length,
      byLevel: {},
      byPlugin: {},
      recentErrors: logs.filter((log) => log.level === "error").slice(-10),
    };

    logs.forEach((log) => {
      stats.byLevel[log.level] = (stats.byLevel[log.level] || 0) + 1;
      stats.byPlugin[log.plugin] = (stats.byPlugin[log.plugin] || 0) + 1;
    });

    return stats;
  }

  showLogs(filter = {}) {
    console.log(`🎯 showLogs() called for ${this.pluginName}`);
    console.log(`🔍 Filter:`, filter);

    if (DEBUG_CONFIG.DEBUG !== 1) {
      console.log("Debug mode is disabled. Set DEBUG_CONFIG.DEBUG = 1 to enable logging.");
      return;
    }

    const logs = this.getLogs();
    console.log(`📊 Retrieved ${logs.length} logs from getLogs()`);
    console.log(`📦 Sample log:`, logs.length > 0 ? logs[0] : "No logs");
    let filteredLogs = logs;

    // Apply filters
    if (filter.level) {
      filteredLogs = filteredLogs.filter((log) => log.level === filter.level);
    }
    if (filter.plugin) {
      filteredLogs = filteredLogs.filter((log) => log.plugin === filter.plugin);
    }
    if (filter.search) {
      const search = filter.search.toLowerCase();
      filteredLogs = filteredLogs.filter(
        (log) =>
          log.message.toLowerCase().includes(search) ||
          JSON.stringify(log.data).toLowerCase().includes(search),
      );
    }

    // Create UI
    this._createLogsUI(filteredLogs);
  }
  showAllLogs() {
    if (DEBUG_CONFIG.DEBUG !== 1) {
      console.log("Debug mode is disabled.");
      return;
    }

    console.log("🔍 Gathering all logs from localStorage...");

    try {
      const storage = this._getStorage();
      const allLogs = [];
      const keys = [];

      // Collect all log entries from all keys
      for (let i = 0; i < storage.length; i++) {
        const key = storage.key(i);
        if (key.startsWith("logs_")) {
          keys.push(key);
          try {
            const logs = JSON.parse(storage.getItem(key));
            if (Array.isArray(logs)) {
              allLogs.push(...logs);
            }
          } catch (e) {
            console.error(`Failed to parse ${key}:`, e);
          }
        }
      }

      // Sort by timestamp
      allLogs.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));

      console.log(`📊 Found ${allLogs.length} logs across ${keys.length} keys`);

      // Show all logs in UI
      this._createLogsUI(allLogs, {
        title: `📊 All Persistent Logs (${allLogs.length} entries from ${keys.length} loggers)`,
      });
    } catch (error) {
      console.error("Failed to show all logs:", error);
    }
  }

  //   _createLogsUI(logs) {
  //     // Remove existing overlay if present
  //     const existing = document.getElementById("persistent-logs-overlay");
  //     if (existing) existing.remove();

  //     const overlay = document.createElement("div");
  //     overlay.id = "persistent-logs-overlay";
  //     overlay.style.cssText = `
  //       position: fixed;
  //       top: 0;
  //       right: 0;
  //       width: 700px;
  //       height: 100vh;
  //       background: #1a1a1a;
  //       color: #f0f0f0;
  //       z-index: 100000;
  //       font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
  //       font-size: 12px;
  //       display: flex;
  //       flex-direction: column;
  //       box-shadow: -5px 0 15px rgba(0,0,0,0.5);
  //     `;

  //     // Header
  //     const header = document.createElement("div");
  //     header.style.cssText = `
  //       padding: 15px;
  //       background: #2a2a2a;
  //       border-bottom: 1px solid #444;
  //       display: flex;
  //       justify-content: space-between;
  //       align-items: center;
  //     `;

  //     const title = document.createElement("h3");
  //     title.textContent = `📊 Persistent Logs (${logs.length} entries)`;
  //     title.style.cssText = "margin: 0; color: #4CAF50; font-size: 14px;";

  //     const closeBtn = document.createElement("button");
  //     closeBtn.textContent = "×";
  //     closeBtn.style.cssText = `
  //       background: #ff4444;
  //       color: white;
  //       border: none;
  //       border-radius: 50%;
  //       width: 30px;
  //       height: 30px;
  //       font-size: 18px;
  //       cursor: pointer;
  //       line-height: 1;
  //     `;
  //     closeBtn.onclick = () => overlay.remove();

  //     header.appendChild(title);
  //     header.appendChild(closeBtn);

  //     // Controls
  //     const controls = document.createElement("div");
  //     controls.style.cssText = `
  //       padding: 10px 15px;
  //       background: #2a2a2a;
  //       border-bottom: 1px solid #444;
  //       display: flex;
  //       gap: 10px;
  //       flex-wrap: wrap;
  //     `;

  //     // Clear button
  //     const clearBtn = document.createElement("button");
  //     clearBtn.textContent = "🗑️ Clear";
  //     clearBtn.style.cssText = `
  //       background: #ff9800;
  //       color: white;
  //       border: none;
  //       padding: 5px 10px;
  //       border-radius: 4px;
  //       cursor: pointer;
  //       font-size: 11px;
  //     `;
  //     clearBtn.onclick = () => {
  //       if (confirm("Clear all logs?")) {
  //         this.clearLogs();
  //         overlay.remove();
  //         this.showLogs();
  //       }
  //     };

  //     // Export button
  //     const exportBtn = document.createElement("button");
  //     exportBtn.textContent = "📤 Export";
  //     exportBtn.style.cssText = `
  //       background: #2196F3;
  //       color: white;
  //       border: none;
  //       padding: 5px 10px;
  //       border-radius: 4px;
  //       cursor: pointer;
  //       font-size: 11px;
  //     `;
  //     exportBtn.onclick = () => this._exportLogs();

  //     // Stats button
  //     const statsBtn = document.createElement("button");
  //     statsBtn.textContent = "📈 Stats";
  //     statsBtn.style.cssText = `
  //       background: #9C27B0;
  //       color: white;
  //       border: none;
  //       padding: 5px 10px;
  //       border-radius: 4px;
  //       cursor: pointer;
  //       font-size: 11px;
  //     `;
  //     statsBtn.onclick = () => {
  //       const stats = this.getStats();
  //       console.log("Log Statistics:", stats);
  //       alert(
  //         `Total logs: ${stats.total}\nErrors: ${stats.byLevel.error || 0}\nWarnings: ${stats.byLevel.warn || 0}`,
  //       );
  //     };

  //     controls.appendChild(clearBtn);
  //     controls.appendChild(exportBtn);
  //     controls.appendChild(statsBtn);

  //     // Logs container
  //     const logsContainer = document.createElement("div");
  //     logsContainer.style.cssText = `
  //       flex: 1;
  //       overflow-y: auto;
  //       padding: 10px;
  //     `;

  //     if (logs.length === 0) {
  //       logsContainer.innerHTML =
  //         '<div style="color: #888; text-align: center; padding: 20px;">No logs found</div>';
  //     } else {
  //       logs.forEach((log, index) => {
  //         const logEntry = document.createElement("div");
  //         logEntry.style.cssText = `
  //           margin-bottom: 10px;
  //           padding: 10px;
  //           background: #2a2a2a;
  //           border-left: 4px solid ${this._getLevelColor(log.level)};
  //           border-radius: 4px;
  //           word-break: break-all;
  //         `;

  //         const time = new Date(log.timestamp).toLocaleTimeString();
  //         const levelColors = {
  //           error: "#ff4444",
  //           warn: "#ff9800",
  //           info: "#2196F3",
  //           debug: "#4CAF50",
  //           trace: "#9C27B0",
  //         };

  //         logEntry.innerHTML = `
  //           <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
  //             <span style="color: #aaa; font-size: 10px;">${time}</span>
  //             <div>
  //               <span style="color: ${levelColors[log.level] || "#fff"}; font-weight: bold; font-size: 10px; margin-right: 10px;">
  //                 ${log.level.toUpperCase()}
  //               </span>
  //               <span style="color: #666; font-size: 10px;">${log.plugin}</span>
  //             </div>
  //           </div>
  //           <div style="color: #f0f0f0; margin-bottom: 5px;">${log.message}</div>
  //           ${
  //             log.data && log.data !== "null"
  //               ? `
  //             <pre style="color: #ccc; background: #000; padding: 5px; border-radius: 3px; overflow-x: auto; font-size: 10px; margin: 5px 0 0 0;">
  //               ${log.data}
  //             </pre>
  //           `
  //               : ""
  //           }
  //         `;

  //         logsContainer.appendChild(logEntry);
  //       });
  //     }

  //     overlay.appendChild(header);
  //     overlay.appendChild(controls);
  //     overlay.appendChild(logsContainer);
  //     document.body.appendChild(overlay);
  //   }
  _createLogsUI(logs, options = {}) {
    console.log(`🎨 Creating UI for ${logs.length} logs`);

    // Remove existing overlay if present
    const existing = document.getElementById("persistent-logs-overlay");
    if (existing) {
      console.log("Removing existing overlay");
      existing.remove();
    }

    const overlay = document.createElement("div");
    overlay.id = "persistent-logs-overlay";
    overlay.style.cssText = `
    position: fixed;
    top: 0;
    right: 0;
    width: 800px;
    height: 100vh;
    background: #1a1a1a;
    color: #f0f0f0;
    z-index: 100000;
    font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    box-shadow: -5px 0 15px rgba(0,0,0,0.5);
  `;

    // Header
    const header = document.createElement("div");
    header.style.cssText = `
    padding: 15px;
    background: #2a2a2a;
    border-bottom: 1px solid #444;
    display: flex;
    justify-content: space-between;
    align-items: center;
  `;

    const title = document.createElement("h3");
    title.textContent = options.title || `📊 Persistent Logs (${logs.length} entries)`;
    title.style.cssText = "margin: 0; color: #4CAF50; font-size: 14px;";

    const closeBtn = document.createElement("button");
    closeBtn.textContent = "×";
    closeBtn.style.cssText = `
    background: #ff4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    font-size: 18px;
    cursor: pointer;
    line-height: 1;
  `;
    closeBtn.onclick = () => overlay.remove();

    header.appendChild(title);
    header.appendChild(closeBtn);

    // Controls
    const controls = document.createElement("div");
    controls.style.cssText = `
    padding: 10px 15px;
    background: #2a2a2a;
    border-bottom: 1px solid #444;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  `;

    // Clear button
    const clearBtn = document.createElement("button");
    clearBtn.textContent = "🗑️ Clear";
    clearBtn.style.cssText = `
    background: #ff9800;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 11px;
  `;
    clearBtn.onclick = () => {
      if (confirm("Clear all logs?")) {
        this.clearLogs();
        overlay.remove();
        // Refresh with empty logs
        this._createLogsUI([], options);
      }
    };

    // Refresh button
    const refreshBtn = document.createElement("button");
    refreshBtn.textContent = "🔄 Refresh";
    refreshBtn.style.cssText = `
    background: #2196F3;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 11px;
  `;
    refreshBtn.onclick = () => {
      overlay.remove();
      this.showLogs();
    };

    // Show All button
    const showAllBtn = document.createElement("button");
    showAllBtn.textContent = "🌐 Show All";
    showAllBtn.style.cssText = `
    background: #9C27B0;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 11px;
  `;
    showAllBtn.onclick = () => {
      this.showAllLogs();
    };

    controls.appendChild(clearBtn);
    controls.appendChild(refreshBtn);
    controls.appendChild(showAllBtn);

    // Logs container
    const logsContainer = document.createElement("div");
    logsContainer.style.cssText = `
    flex: 1;
    overflow-y: auto;
    padding: 10px;
  `;

    if (!Array.isArray(logs)) {
      console.error("Logs is not an array:", typeof logs);
      logsContainer.innerHTML = `
      <div style="color: #ff4444; text-align: center; padding: 20px;">
        Error: logs is not an array (${typeof logs})
      </div>
    `;
    } else if (logs.length === 0) {
      logsContainer.innerHTML = `
      <div style="color: #888; text-align: center; padding: 20px;">
        No logs found
      </div>
    `;
    } else {
      console.log(`Rendering ${logs.length} log entries`);

      // Create document fragment for better performance
      const fragment = document.createDocumentFragment();

      logs.forEach((log, index) => {
        const logEntry = document.createElement("div");
        logEntry.style.cssText = `
        margin-bottom: 10px;
        padding: 10px;
        background: #2a2a2a;
        border-left: 4px solid ${this._getLevelColor(log.level)};
        border-radius: 4px;
        word-break: break-all;
      `;

        const time = new Date(log.timestamp).toLocaleTimeString();
        const date = new Date(log.timestamp).toLocaleDateString();

        // Clean up data display
        let dataDisplay = "";
        if (log.data && log.data !== "null" && log.data !== "undefined") {
          try {
            // Try to parse JSON if it looks like JSON
            if (
              typeof log.data === "string" &&
              (log.data.startsWith("{") || log.data.startsWith("["))
            ) {
              const parsed = JSON.parse(log.data);
              dataDisplay = JSON.stringify(parsed, null, 2);
            } else {
              dataDisplay = String(log.data);
            }
          } catch (e) {
            dataDisplay = String(log.data);
          }
        }

        logEntry.innerHTML = `
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
          <div>
            <span style="color: #aaa; font-size: 10px;">${date} ${time}</span>
            <span style="color: #666; font-size: 10px; margin-left: 10px;">${log.plugin || "Unknown"}</span>
          </div>
          <span style="color: ${this._getLevelColor(log.level)}; font-weight: bold; font-size: 10px;">
            ${(log.level || "info").toUpperCase()}
          </span>
        </div>
        <div style="color: #f0f0f0; margin-bottom: 5px; font-size: 11px;">${log.message || "No message"}</div>
        ${
          dataDisplay
            ? `
          <div style="margin-top: 5px;">
            <details style="color: #ccc;">
              <summary style="cursor: pointer; font-size: 10px; color: #888;">View data</summary>
              <pre style="background: #000; padding: 8px; border-radius: 3px; overflow-x: auto; font-size: 10px; margin: 5px 0 0 0; max-height: 200px; overflow-y: auto;">
                ${dataDisplay}
              </pre>
            </details>
          </div>
        `
            : ""
        }
      `;

        fragment.appendChild(logEntry);
      });

      logsContainer.appendChild(fragment);
    }

    overlay.appendChild(header);
    overlay.appendChild(controls);
    overlay.appendChild(logsContainer);
    document.body.appendChild(overlay);

    console.log("✅ UI created successfully");
  }

  _getLevelColor(level) {
    const colors = {
      error: "#ff4444",
      warn: "#ff9800",
      info: "#2196F3",
      success: "#4CAF50",
      debug: "#4CAF50",
      trace: "#9C27B0",
    };
    return colors[level] || "#666";
  }
  _getLevelColor(level) {
    const colors = {
      error: "#ff4444",
      warn: "#ff9800",
      info: "#2196F3",
      debug: "#4CAF50",
      trace: "#9C27B0",
    };
    return colors[level] || "#666";
  }

  _exportLogs() {
    const logs = this.getLogs();
    const dataStr = JSON.stringify(logs, null, 2);
    const dataUri = "data:application/json;charset=utf-8," + encodeURIComponent(dataStr);
    const exportFileDefaultName = `logs_${this.pluginName}_${new Date().toISOString().replace(/[:.]/g, "-")}.json`;

    const linkElement = document.createElement("a");
    linkElement.setAttribute("href", dataUri);
    linkElement.setAttribute("download", exportFileDefaultName);
    linkElement.click();
  }

  _checkForErrors() {
    const logs = this.getLogs();
    const recentErrors = logs.filter(
      (log) =>
        log.level === "error" && new Date(log.timestamp) > new Date(Date.now() - 5 * 60 * 1000), // Last 5 minutes
    );

    if (recentErrors.length > 0) {
      console.warn(`Found ${recentErrors.length} recent errors from previous session.`);
      // Optionally auto-show logs
      if (this.options.autoShowOnError) {
        setTimeout(() => this.showLogs({ level: "error" }), 1000);
      }
    }
  }
}

// Optional: Create a global instance for easy access
if (typeof window !== "undefined" && DEBUG_CONFIG.DEBUG === 1) {
  window.PersistentLogger = PersistentLogger;
  window.persistentLog = new PersistentLogger("Global", {
    persistLevel: "debug", // Persist everything including debug logs
    storageType: "localStorage", // Keep logs across browser sessions
  });
}
