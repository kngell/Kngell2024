import { generateUniqueId, clearInputValues } from "js/core/utils/domHelpers";

class VariationHandler {
  constructor() {
    this.variationContainer = document.querySelector(".frm-section.variation .frm-section__body");
    this.init();
  }

  init() {
    if (!this.variationContainer) return;

    // Listen to card-action events for attribute reindexing
    document.addEventListener("card-action:item-added", (e) => {
      const { selector, target } = e.detail;
      if (selector === ".variation-attributes") {
        this.reindexAttributes(target);
        this.regenerateAttributeIds(target); // ← Add this
      }
    });

    document.addEventListener("card-action:item-removed", (e) => {
      const { selector, target } = e.detail;
      if (selector === ".variation-attributes") {
        this.reindexAttributes(target);
      }
    });

    this.setupEventDelegation();
  }

  setupEventDelegation() {
    this.variationContainer.addEventListener("click", (e) => {
      const addBtn = e.target.closest('[data-action="add-variation-group"]');
      if (addBtn) {
        e.preventDefault();
        this.addVariationGroup();
        return;
      }

      const removeGroupBtn = e.target.closest('[data-action="remove-group"]');
      if (removeGroupBtn) {
        e.preventDefault();
        const groupId = removeGroupBtn.getAttribute("data-group-id");
        if (groupId) {
          this.removeVariationGroup(groupId);
        }
        return;
      }
    });
  }

  addVariationGroup() {
    const existingGroup = this.variationContainer.querySelector(".variation-group");
    if (!existingGroup) return;

    const currentGroups = this.variationContainer.querySelectorAll(".variation-group");
    const newGroup = existingGroup.cloneNode(true);
    const newIndex = currentGroups.length;

    // Update variation indexes
    this.updateVariationIndexes(newGroup, newIndex);

    // Regenerate ALL IDs in the new group to avoid duplicates
    this.regenerateAllIds(newGroup);

    // Update remove button
    const removeBtn = newGroup.querySelector('[data-action="remove-group"]');
    if (removeBtn) {
      removeBtn.setAttribute("data-group-id", (newIndex + 1).toString());
    }

    // Clear all values
    clearInputValues(newGroup);

    // Reset attributes to single group with index 0
    this.resetAttributesToSingle(newGroup);

    this.variationContainer.appendChild(newGroup);
    this.reindexAllVariations();
  }

  removeVariationGroup(groupId) {
    const groupIndex = parseInt(groupId) - 1;
    const groups = this.variationContainer.querySelectorAll(".variation-group");

    if (groupIndex >= 0 && groupIndex < groups.length) {
      if (groups.length === 1) {
        clearInputValues(groups[0]);
        this.resetAttributesToSingle(groups[0]);
        this.regenerateAllIds(groups[0]); // ← Regenerate IDs after clearing
        return;
      }

      groups[groupIndex].remove();
      this.reindexAllVariations();
    }
  }

  // ─── ID Regeneration ─────────────────────────────────────

  /**
   * Regenerate all IDs within an element to avoid duplicates
   */
  regenerateAllIds(container) {
    const allElementsWithId = container.querySelectorAll("[id]");
    allElementsWithId.forEach((element) => {
      const oldId = element.id;
      const newId = this.generateFreshId(oldId);
      element.id = newId;

      // Update any label that references this ID
      const label = container.querySelector(`label[for="${oldId}"]`);
      if (label) {
        label.setAttribute("for", newId);
      }
    });
  }

  /**
   * Regenerate IDs specifically for attribute groups after cloning
   */
  regenerateAttributeIds(attributesField) {
    const attributeGroups = attributesField.querySelectorAll(".variation-attributes");
    attributeGroups.forEach((group) => {
      const inputs = group.querySelectorAll("input");
      inputs.forEach((input) => {
        if (input.id) {
          const oldId = input.id;
          const newId = this.generateFreshId(oldId);
          input.id = newId;

          // Update associated label
          const label = group.querySelector(`label[for="${oldId}"]`);
          if (label) {
            label.setAttribute("for", newId);
          }
        }
      });
    });
  }

  /**
   * Generate a fresh ID based on the old one
   */
  generateFreshId(oldId) {
    // Pattern: product-frm_attribute-name_2 → product-frm_attribute-name_XXXXX
    // Or: product-frm_variation-name_24 → product-frm_variation-name_XXXXX
    const parts = oldId.split("_");
    if (parts.length > 1) {
      // Remove the last part (old number) and add new unique number
      parts.pop();
      return `${parts.join("_")}_${generateUniqueId()}`;
    }
    return `${oldId}_${generateUniqueId()}`;
  }

  // ─── Variation Indexing ───────────────────────────────────

  reindexAllVariations() {
    const groups = this.variationContainer.querySelectorAll(".variation-group");
    groups.forEach((group, idx) => {
      this.updateVariationIndexes(group, idx);

      const removeBtn = group.querySelector('[data-action="remove-group"]');
      if (removeBtn) {
        removeBtn.setAttribute("data-group-id", (idx + 1).toString());
      }
    });
  }

  updateVariationIndexes(group, newIndex) {
    const inputs = group.querySelectorAll("[name]");
    inputs.forEach((input) => {
      const name = input.getAttribute("name");
      if (name && name.includes("variations[")) {
        const newName = name.replace(/variations\[\d+\]/, `variations[${newIndex}]`);
        input.setAttribute("name", newName);
      }
    });
  }

  // ─── Attribute Indexing ───────────────────────────────────

  // ─── Attribute Indexing ───────────────────────────────────

  reindexAttributes(attributesField) {
    // Reindex all attribute groups to ensure sequential order
    const groups = attributesField.querySelectorAll(".variation-attributes");
    console.log(`[VariationHandler] Reindexing ${groups.length} attribute groups`);

    groups.forEach((group, idx) => {
      const inputs = group.querySelectorAll("[name]");
      inputs.forEach((input) => {
        const name = input.getAttribute("name");
        if (name && name.includes("attributes[")) {
          // Use the same pattern as CardActionHandler
          const newName = name.replace(/\[attributes\]\[\d+\]/, `[attributes][${idx}]`);
          if (name !== newName) {
            input.setAttribute("name", newName);
            console.log(`[VariationHandler] Reindexed: ${name} -> ${newName}`);
          }
        }
      });
    });

    // Then regenerate IDs
    this.regenerateAttributeIds(attributesField);
  }
  // ─── Helpers ─────────────────────────────────────────────

  resetAttributesToSingle(variationGroup) {
    const attributesField = variationGroup.querySelector(".attributes-field");
    if (!attributesField) return;

    const attributeGroups = attributesField.querySelectorAll(".variation-attributes");

    // Remove all but first
    for (let i = attributeGroups.length - 1; i > 0; i--) {
      attributeGroups[i].remove();
    }

    // Reset first group to index 0
    if (attributeGroups[0]) {
      clearInputValues(attributeGroups[0]);
      const inputs = attributeGroups[0].querySelectorAll("[name]");
      inputs.forEach((input) => {
        const name = input.getAttribute("name");
        if (name && name.includes("attributes[")) {
          // Use the correct pattern
          const newName = name.replace(/\[attributes\]\[\d+\]/, "[attributes][0]");
          input.setAttribute("name", newName);
        }
      });
      // Regenerate IDs for the reset group
      this.regenerateAttributeIds(attributesField);
    }
  }
}

export default VariationHandler;
