import BrowserLogger from "./BrowserLogger";

let instance = null;

export default class AjaxHandler {
  constructor(options = {}) {
    if (instance) {
      return instance;
    }

    this.logger = new BrowserLogger("AjaxHandler");
    this.defaults = {
      method: "GET",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      timeout: 30000,
      ...options
    };

    instance = this;
    return instance;
  }

  async request(customOptions = {}) {
    const options = { ...this.defaults, ...customOptions };

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

      // ✅ Parse and return response to caller
      return await this.parseResponse(response);
    } catch (error) {
      clearTimeout(timeoutId);

      // ✅ Return error as a structured response
      return {
        success: false,
        error: this.formatError(error, options),
        status: error?.status ?? null,
        _isError: true
      };
    }
  }

  async parseResponse(response) {
    const responseText = await response.text();
    const contentType = response.headers.get("content-type") || "";
    const isJson = contentType.includes("application/json");

    this.logger.debug("Response received:", {
      status: response.status,
      ok: response.ok,
      contentType,
      url: response.url,
      textLength: responseText.length
    });

    if (isJson && responseText) {
      try {
        const jsonData = JSON.parse(responseText);
        return {
          ...jsonData,
          status: response.status,
          ok: response.ok,
          _parsed: true
        };
      } catch (parseError) {
        this.logger.error("JSON parse error:", parseError);
        // Return raw response if JSON parsing fails
        return {
          data: responseText,
          status: response.status,
          ok: response.ok,
          _parsed: false,
          _parseError: parseError.message
        };
      }
    }

    // ✅ Return plain text for non-JSON responses
    return {
      data: responseText,
      status: response.status,
      ok: response.ok,
      _parsed: false
    };
  }

  formatError(error, options) {
    let errorMessage = "An error occurred";

    if (error.name === "AbortError") {
      errorMessage = `Request to ${options.url} timed out after ${options.timeout}ms`;
    } else if (error.message?.includes("Failed to fetch")) {
      errorMessage = `Network error: Cannot connect to ${options.url}`;
    } else {
      errorMessage = error.message || errorMessage;
    }

    return errorMessage;
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
