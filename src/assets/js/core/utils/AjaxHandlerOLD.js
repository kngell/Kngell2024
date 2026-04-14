import BrowserLogger from "./BrowserLogger";

export default class AjaxHandlerOLD {
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

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), options.timeout);

    try {
      this.logger.info(`Making ${options.method} request to ${options.url}`);

      const config = {
        method: options.method,
        headers: { ...options.headers },
        signal: controller.signal,
        credentials: "same-origin",
        redirect: "follow" // Follow redirects automatically
      };

      if (options.method !== "GET" && options.data) {
        if (options.data instanceof FormData) {
          config.body = options.data;
          // Don't set Content-Type for FormData - browser will set it with boundary
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

    if (options.json && isJson) {
      try {
        const jsonData = JSON.parse(responseText);

        this.logger.debug("Parsed JSON response:", jsonData);

        // Handle your new JSON response format with redirects
        if (jsonData.redirect) {
          this.logger.debug("JSON response contains redirect:", jsonData.redirect);
          return {
            ...jsonData,
            redirected: true,
            finalUrl: jsonData.redirect,
            requiresFullReload: false
          };
        }

        // if (jsonData.success === false || jsonData.error) {
        //   throw new Error(jsonData.error || jsonData.message || "Request failed");
        // }

        return jsonData;
      } catch (error) {
        this.logger.error("JSON parse error:", error);
        throw new Error(`Invalid JSON response: ${responseText.substring(0, 100)}`);
      }
    }

    if (options.json && isHtml && response.ok) {
      this.logger.debug("Processing HTML response as fallback");

      // Check if it's a success page (common after form submissions)
      const successIndicators = ["success", "deleted", "completed", "saved", "updated"];
      const hasSuccessIndicator = successIndicators.some((indicator) =>
        responseText.toLowerCase().includes(indicator)
      );

      if (hasSuccessIndicator || response.redirected) {
        const result = {
          success: true,
          html: responseText,
          redirected: response.redirected,
          finalUrl: response.url,
          message: "Operation completed successfully"
        };

        // Try to extract flash messages from HTML
        const flashMessages = this.extractFlashMessages(responseText);
        if (flashMessages.length > 0) {
          result.flashMessages = flashMessages;
          // HTML redirects usually need full reload to preserve session data
          result.requiresFullReload = true;
        }

        return result;
      }
    }

    if (response.ok && (!responseText || responseText.trim() === "")) {
      this.logger.debug("Empty successful response");
      return {
        success: true,
        empty: true,
        status: response.status,
        message: "Request completed successfully"
      };
    }

    if (!response.ok) {
      let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
      let errorDetails = { status: response.status, statusText: response.statusText };

      if (responseText) {
        try {
          // Try to parse as JSON error
          const errorData = JSON.parse(responseText);
          errorMessage = errorData.error || errorData.message || errorMessage;
          errorDetails = { ...errorDetails, ...errorData };
        } catch (e) {
          // Not JSON, use text
          errorMessage = responseText.substring(0, 200);
        }
      }

      this.logger.error("Request failed:", errorMessage, errorDetails);
      throw new Error(errorMessage);
    }

    if (options.json) {
      try {
        return JSON.parse(responseText);
      } catch (error) {
        // If not JSON but response is OK, return as success
        if (response.ok) {
          return {
            success: true,
            text: responseText,
            message: "Request completed successfully"
          };
        }
        throw new Error(`Invalid JSON response: ${responseText.substring(0, 100)}`);
      }
    }

    // Return raw text for non-JSON requests
    return responseText;
  }

  // Helper method to extract flash messages from HTML
  extractFlashMessages(html) {
    const messages = [];

    try {
      // Simple DOM parsing for flash messages
      const flashRegex = /<div[^>]*class="[^"]*flash[^"]*"[^>]*>([\s\S]*?)<\/div>/gi;
      const alertRegex = /<div[^>]*class="[^"]*alert[^"]*"[^>]*>([\s\S]*?)<\/div>/gi;
      const messageRegex = /<div[^>]*class="[^"]*message[^"]*"[^>]*>([\s\S]*?)<\/div>/gi;

      const allRegexes = [flashRegex, alertRegex, messageRegex];

      allRegexes.forEach((regex) => {
        let match;
        while ((match = regex.exec(html)) !== null) {
          const htmlContent = match[1];
          // Extract text content (crude but works)
          const text = htmlContent.replace(/<[^>]*>/g, "").trim();
          if (text) {
            // Try to determine type from classes
            const classMatch = match[0].match(/class="([^"]*)"/);
            let type = "info";
            if (classMatch) {
              const classes = classMatch[1];
              if (classes.includes("success") || classes.includes("green")) type = "success";
              if (
                classes.includes("error") ||
                classes.includes("danger") ||
                classes.includes("red")
              )
                type = "error";
              if (classes.includes("warning") || classes.includes("yellow")) type = "warning";
            }
            messages.push({ type, text, html: match[0] });
          }
        }
      });
    } catch (error) {
      this.logger.debug("Could not extract flash messages from HTML:", error);
    }

    return messages;
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

    this.logger.error(`Request to ${options.url} failed:`, errorMessage, errorDetails);

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
