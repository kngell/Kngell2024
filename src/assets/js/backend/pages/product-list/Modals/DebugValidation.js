export default class DebugValidation {
  static debugFormValidator(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) {
      console.error("Form not found:", formSelector);
      return;
    }

    const formValidator = form._validator;
    if (!formValidator) {
      console.error("FormValidator not found on form");
      return;
    }

    console.log("🔍 Debugging FormValidator");
    console.log("=".repeat(60));

    // Access public properties
    console.log("FormValidator Properties:");
    console.log("  submissionMode:", formValidator.submissionMode);
    console.log("  rulesName:", formValidator.rulesName);
    console.log("  form:", formValidator.form?.name || formValidator.form?.id);

    // Access validator through public methods if available
    if (formValidator.validator) {
      console.log("\nValidator Rules:");
      const rules = formValidator.validator.rules || {};
      console.log("  Total rules:", Object.keys(rules).length);

      // Check specific field
      const fieldName = "confirm_delete";
      if (rules[fieldName]) {
        console.log(`\nRules for ${fieldName}:`);
        Object.entries(rules[fieldName]).forEach(([rule, value]) => {
          console.log(`  ${rule}: ${value} (${typeof value})`);
        });
      }

      // Test validation
      console.log("\nTest Validation:");
      try {
        // Use public method if available
        if (typeof formValidator.validateField === "function") {
          const checkbox = form.querySelector(`[name="${fieldName}"]`);
          const isValid = formValidator.validateField(checkbox);
          console.log(`  validateField result: ${isValid ? "✅" : "❌"}`);
        }
      } catch (e) {
        console.log("  Could not run validateField:", e.message);
      }
    }

    console.log("\n" + "=".repeat(60));
  }

  static debugCheckbox(fieldName) {
    const checkbox = document.querySelector(`[name="${fieldName}"]`);
    if (!checkbox) {
      console.error("Checkbox not found:", fieldName);
      return;
    }

    console.log(`🔍 Debugging Checkbox: ${fieldName}`);
    console.log("=".repeat(60));

    console.log("DOM Properties:");
    console.log("  checked:", checkbox.checked);
    console.log("  value:", checkbox.value);
    console.log("  type:", checkbox.type);
    console.log("  name:", checkbox.name);
    console.log("  has value attribute:", checkbox.hasAttribute("value"));

    // Check form data
    const form = checkbox.closest("form");
    if (form) {
      const formData = new FormData(form);
      console.log("\nFormData State:");
      console.log("  In FormData:", formData.has(fieldName));
      console.log("  FormData value:", formData.get(fieldName));

      // Check FormValidator if exists
      if (form._validator) {
        console.log("\nFormValidator Integration:");
        const processor = form._validator.dataProcessor;
        if (processor && typeof processor.processFormData === "function") {
          const processed = processor.processFormData(form);
          console.log(
            `  Processed value: ${processed[fieldName]} (${typeof processed[fieldName]})`,
          );
        }
      }
    }

    console.log("\n" + "=".repeat(60));
  }
}

// Make it globally available
if (typeof window !== "undefined") {
  window.DebugValidation = DebugValidation;
}
