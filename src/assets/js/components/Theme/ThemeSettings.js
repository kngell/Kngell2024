import BrowserLogger from "js/core/utils/BrowserLogger";
import RadioOptions from "js/components/Options/RadioOptions";

export default class ThemeSettings {
  constructor(container, options = {}) {
    this.container = container;
    this.logger = new BrowserLogger("ThemeSettings");
    this.radioOptions = null;
    this.apiEndpoint = options.apiEndpoint || "/api/user/preferences";
    this.userId = options.userId || null;
    this.onSave = options.onSave || null;

    this.init();
  }

  init() {
    if (!this.container) {
      this.logger.error("Container element not provided");
      return;
    }

    this.initializeRadioOptions();
    this.bindSaveButton();
  }

  initializeRadioOptions() {
    const optionsContainer = this.container.querySelector(".options");
    if (!optionsContainer) {
      this.logger.error("Options container not found");
      return;
    }

    // Get current theme from data attribute
    const currentTheme = this.container.dataset.currentTheme || "light";

    this.radioOptions = new RadioOptions(optionsContainer, {
      name: "theme_preference",
      value: currentTheme,
      onChange: (event) => {
        this.logger.debug("Theme preference changed:", event.value);
        // Optionally preview the change
        if (this.config.previewOnChange) {
          this.previewTheme(event.value);
        }
      }
    });
  }

  bindSaveButton() {
    const saveButton = this.container.querySelector('[data-action="save-theme"]');
    if (saveButton) {
      saveButton.addEventListener("click", async (e) => {
        e.preventDefault();
        await this.saveThemePreference();
      });
    }
  }

  async saveThemePreference() {
    const selectedTheme = this.radioOptions.getValue();

    if (!selectedTheme) {
      this.logger.warn("No theme selected");
      return;
    }

    try {
      const response = await fetch(this.apiEndpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
          preference: "theme",
          value: selectedTheme,
          user_id: this.userId
        })
      });

      const result = await response.json();

      if (result.success) {
        this.logger.success("Theme preference saved", { theme: selectedTheme });

        if (this.onSave) {
          this.onSave(selectedTheme, result);
        }

        // Show success message
        this.showMessage("Theme preference saved successfully", "success");
      } else {
        throw new Error(result.error || "Failed to save theme preference");
      }
    } catch (error) {
      this.logger.error("Failed to save theme preference:", error);
      this.showMessage("Failed to save theme preference", "error");
    }
  }

  previewTheme(theme) {
    // Optional: preview theme without saving
    document.documentElement.classList.remove("theme-light", "theme-dark");
    document.documentElement.classList.add(`theme-${theme}`);
  }

  showMessage(message, type) {
    // Simple notification - you can use your notification helper
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.remove();
    }, 3000);
  }

  getCurrentValue() {
    return this.radioOptions ? this.radioOptions.getValue() : null;
  }

  destroy() {
    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }
  }
}
