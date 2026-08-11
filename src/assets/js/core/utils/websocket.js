import BrowserLogger from "js/core/utils/logger";
const logger = new BrowserLogger("webSocket");
class WebSocket {
  constructor() {
    this._init();
  }

  _init = () => {
    const protocol = window.location.protocol === "https:" ? "wss:" : "ws:";
    const wsHost = window.location.host;
    const wsUrl = `${protocol}//${wsHost}/ws`;

    let ws;
    function connectWebSocket() {
      ws = new WebSocket(wsUrl);

      ws.onopen = function () {
        logger.info("🔌 WebSocket: Connection opened...");
        logger.debug("", {
          timestamp: Date.now(),
          connectionCount: (window._wsConnectionCount = (window._wsConnectionCount || 0) + 1)
        });
      };

      ws.onmessage = function (event) {
        const data = JSON.parse(event.data);
        if (data.type === "full-reload") {
          logger.debug(
            "🔌 WebSocket: FULL RELOAD received; letting webpack-dev-server handle it.",
            data.reason
          );
          return;
        } else if (data.type === "css-update") {
          logger.debug("🔌 WebSocket: CSS UPDATE TRIGGERED", data.reason);
          const links = document.querySelectorAll("link[rel='stylesheet']");
          links.forEach((link) => {
            const url = new URL(link.href);
            url.searchParams.set("reload", Date.now());
            link.href = url.href;
          });
        }
      };

      ws.onclose = function (event) {
        logger.info("🔌 WebSocket: Connection closed...");
        logger.debug("", {
          code: event.code,
          reason: event.reason,
          wasClean: event.wasClean
        });

        if (!event.wasClean && event.code !== 1000) {
          logger.debug("🔌 WebSocket: Attempting to reconnect...");
          setTimeout(connectWebSocket, 3000);
        }
      };

      ws.onerror = function (error) {
        logger.error("🔌 WebSocket: Error", error);
      };
    }

    connectWebSocket(); // Initial connection
  };
}
new WebSocket();
