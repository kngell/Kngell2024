import BrowserLogger from "js/core/utils/BrowserLogger";

/**
 * RadioOptions - A reusable component for radio-based option selection
 */
export default class RadioOptions {
  constructor(container, options = {}) {
    this.container = container;
    this.logger = new BrowserLogger("RadioOptions");

    this.config = {
      onChange: null,
      onInit: null,
      value: null,
      ...options
    };

    this.options = [];
    this.currentValue = null;
    this.initialized = false;
    this.groupName = null;

    if (this.config.autoInitialize !== false) {
      this.init();
    }
  }

  init() {
    if (this.initialized) return;

    this.scanOptions();
    this.bindEvents();
    this.setInitialValue();

    this.initialized = true;

    if (this.config.onInit) {
      this.config.onInit(this.currentValue, this.getSelectedOption());
    }
  }

  scanOptions() {
    const optionElements = this.container.querySelectorAll(".options-box");

    // First, remove all selected classes to start fresh
    optionElements.forEach((el) => el.classList.remove("selected"));

    this.options = Array.from(optionElements).map((element) => {
      const radio = element.querySelector('input[type="radio"]');
      // Normalize value to lowercase
      const value = radio ? radio.value.toLowerCase() : element.dataset.option?.toLowerCase();

      // Read the group name from the first radio input
      if (radio && this.groupName === null) {
        this.groupName = radio.name;
      }

      // Only rely on the checked attribute (normalize radio value for comparison)
      const isSelected = radio && radio.checked;

      // Add selected class to the checked one
      if (isSelected) {
        element.classList.add("selected");
      }

      return {
        element,
        radio,
        value,
        selected: isSelected
      };
    });

    // Find current value from selected options
    const selectedOption = this.options.find((opt) => opt.selected);
    if (selectedOption) {
      this.currentValue = selectedOption.value;
    }
  }

  bindEvents() {
    this.options.forEach((option) => {
      // Click handler for the option box
      option.element.addEventListener("click", (e) => {
        e.preventDefault();
        this.selectOption(option.value);
      });

      // Change handler for the radio input
      if (option.radio) {
        option.radio.addEventListener("change", (e) => {
          if (option.radio.checked) {
            this.selectOption(option.value);
          }
        });
      }
    });
  }

  selectOption(value) {
    // Normalize the incoming value to lowercase
    const normalizedValue = value.toLowerCase();
    const targetOption = this.options.find((opt) => opt.value === normalizedValue);

    if (!targetOption) {
      this.logger.warn("Attempted to select non-existent option", { value: normalizedValue });
      return false;
    }

    // Don't do anything if already selected
    if (this.currentValue === normalizedValue) {
      return false;
    }

    const previousValue = this.currentValue;
    const previousOption = this.getSelectedOption();

    // Deselect all options
    this.options.forEach((option) => {
      if (option.selected) {
        option.element.classList.remove("selected");
        if (option.radio) {
          option.radio.checked = false;
        }
        option.selected = false;
      }
    });

    // Select the target option
    targetOption.element.classList.add("selected");
    if (targetOption.radio) {
      targetOption.radio.checked = true;
    }
    targetOption.selected = true;
    this.currentValue = targetOption.value;

    // Trigger change callback
    if (this.config.onChange) {
      this.config.onChange({
        value: this.currentValue,
        previousValue: previousValue,
        option: targetOption,
        previousOption: previousOption
      });
    }

    return true;
  }

  getValue() {
    return this.currentValue;
  }

  getGroupName() {
    return this.groupName;
  }

  getSelectedOption() {
    return this.options.find((opt) => opt.selected) || null;
  }

  setValue(value) {
    // Normalize the value to lowercase
    const normalizedValue = value?.toLowerCase();
    if (normalizedValue === this.currentValue) return;

    const option = this.options.find((opt) => opt.value === normalizedValue);
    if (!option) return false;

    return this.selectOption(normalizedValue);
  }

  setInitialValue() {
    // Normalize config value to lowercase
    const normalizedConfigValue = this.config.value?.toLowerCase();
    if (normalizedConfigValue && normalizedConfigValue !== this.currentValue) {
      this.setValue(normalizedConfigValue);
    }
  }

  destroy() {
    this.options.forEach((option) => {
      const newElement = option.element.cloneNode(true);
      option.element.parentNode?.replaceChild(newElement, option.element);
    });

    this.options = [];
    this.currentValue = null;
    this.groupName = null;
    this.initialized = false;
  }
}
