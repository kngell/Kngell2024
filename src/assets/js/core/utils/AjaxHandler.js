import BrowserLogger from "./BrowserLogger";

export default class AjaxHandler {
  constructor(options = {}) {
    this.defaults = {
      method: "GET",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      json: true,
      timeout: 30000,
      ...options
    };
    this.logger = new BrowserLogger("AjaxHandler");
  }

  async request(customOptions = {}) {
    const options = { ...this.defaults, ...customOptions };

    // ═══════════════════════════════════════════════════
    // FIX #1: Guard against undefined overriding defaults
    // ═══════════════════════════════════════════════════
    if (options.json === undefined) {
      options.json = this.defaults.json;
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), options.timeout);

    try {
      this.logger.info(`Making ${options.method} request to ${options.url}`);

      const config = {
        method: options.method,
        headers: { ...options.headers },
        signal: controller.signal,
        credentials: "same-origin",
        redirect: "manual"
      };

      if (options.method !== "GET" && options.data) {
        if (options.data instanceof FormData) {
          config.body = options.data;
          if (config.headers["Content-Type"]) {
            delete config.headers["Content-Type"];
          }
        } else if (typeof options.data === "string" && options.data.includes("=")) {
          config.body = options.data;
          config.headers["Content-Type"] = "application/x-www-form-urlencoded";
        } else {
          config.body = JSON.stringify(options.data);
          config.headers["Content-Type"] = "application/json";
        }
      }

      const response = await fetch(options.url, config);
      clearTimeout(timeoutId);

      return await this.handleResponse(response, options);
    } catch (error) {
      clearTimeout(timeoutId);
      return this.handleError(error, options);
    }
  }

  async handleResponse(response, options) {
    if (response.status === 302 || response.status === 301 || response.type === "opaqueredirect") {
      const redirectUrl = response.headers.get("Location");
      this.logger.debug("HTTP Redirect detected:", redirectUrl);

      return {
        success: true,
        redirected: true,
        redirectType: "http",
        redirectUrl: redirectUrl,
        status: response.status,
        message: "Redirect detected"
      };
    }

    const responseText = await response.text();
    const contentType = response.headers.get("content-type") || "";
    const isHtml = contentType.includes("text/html");
    const isJson = contentType.includes("application/json");

    this.logger.debug("Response received:", {
      status: response.status,
      ok: response.ok,
      contentType: contentType,
      redirected: response.redirected,
      url: response.url,
      textLength: responseText.length
    });

    // ═══════════════════════════════════════════════════════════
    // FIX #2: Parse JSON ONCE upfront if content type is JSON.
    //         This eliminates the double-parse and ensures the
    //         full body is ALWAYS available for error responses.
    // ═══════════════════════════════════════════════════════════
    if (isJson && responseText) {
      let jsonData;

      try {
        jsonData = JSON.parse(responseText);
      } catch (parseError) {
        this.logger.error("JSON parse error:", parseError);
        throw new Error(`Invalid JSON response: ${responseText.substring(0, 100)}`);
      }

      this.logger.debug("Parsed JSON response:", jsonData);

      // Handle JSON redirect
      if (jsonData.redirect) {
        this.logger.debug("JSON response contains redirect:", jsonData.redirect);
        return {
          ...jsonData,
          redirected: true,
          redirectType: "json",
          finalUrl: jsonData.redirect
        };
      }

      // ───────────────────────────────────────────────
      // For error responses (422, 400, 500, etc.):
      // Return the FULL parsed body — don't throw.
      // The caller (processAjaxResult) knows how to
      // handle { success: false, errors: { ... } }.
      // ───────────────────────────────────────────────
      if (!response.ok) {
        this.logger.warn(
          `Server returned ${response.status}:`,
          jsonData.error || jsonData.message || response.statusText
        );

        // Ensure the envelope always has a consistent shape
        return {
          success: false,
          ...jsonData, // ← The FULL server response, including `errors`
          status: response.status
        };
      }

      // Happy path: 2xx with JSON
      return jsonData;
    }

    // ── Non-JSON responses below ──

    // Handle empty successful responses
    if (response.ok && (!responseText || responseText.trim() === "")) {
      this.logger.debug("Empty successful response");
      return {
        success: true,
        empty: true,
        status: response.status,
        message: "Request completed successfully"
      };
    }

    // Handle non-JSON error responses
    if (!response.ok) {
      const errorMessage = responseText
        ? responseText.substring(0, 200)
        : `HTTP ${response.status}: ${response.statusText}`;

      this.logger.error("Request failed:", errorMessage);
      throw new Error(errorMessage);
    }

    // Fallback: return text
    return responseText;
  }

  handleError(error, options) {
    let errorMessage = "An error occurred";
    let errorDetails = {};

    if (error.name === "AbortError") {
      errorMessage = `Request to ${options.url} timed out after ${options.timeout}ms`;
      errorDetails = { type: "timeout", timeout: options.timeout };
    } else if (error.message.includes("Failed to fetch")) {
      errorMessage = `Network error: Cannot connect to ${options.url}`;
      errorDetails = { type: "network", url: options.url };
    } else {
      errorMessage = error.message || errorMessage;
      errorDetails = { type: "unknown", originalError: error };
    }

    this.logger.error(`Request to ${options.url} failed:`, errorMessage);

    return {
      success: false,
      error: errorMessage,
      details: errorDetails,
      originalError: error
    };
  }

  async get(url, data = null, options = {}) {
    let fullUrl = url;

    if (data && typeof data === "object") {
      const params = new URLSearchParams(data).toString();
      fullUrl = `${url}?${params}`;
    }

    return this.request({
      url: fullUrl,
      method: "GET",
      ...options
    });
  }

  async post(url, data = {}, options = {}) {
    return this.request({
      url,
      method: "POST",
      data,
      ...options
    });
  }

  async postForm(url, formElement, options = {}) {
    const params = new URLSearchParams();
    const inputs = formElement.querySelectorAll("input, select, textarea");

    inputs.forEach((element) => {
      if (!element.name || element.disabled) return;
      if (element.type === "checkbox" || element.type === "radio") {
        if (element.checked) {
          params.append(element.name, element.value || "on");
        }
      } else if (element.type === "select-multiple") {
        Array.from(element.selectedOptions).forEach((option) => {
          params.append(element.name, option.value);
        });
      } else {
        params.append(element.name, element.value);
      }
    });

    return this.request({
      url,
      method: "POST",
      data: params.toString(),
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        ...options.headers
      },
      ...options
    });
  }
}
