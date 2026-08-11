// js/components/cardAction/card-action-handler.js

class CardActionHandler {
  constructor() {
    this.init();
  }

  init() {
    document.addEventListener("click", (e) => {
      const button = e.target.closest("[data-card-action]");
      if (!button) return;

      const cardAction = button.closest(".card-action");
      if (!cardAction) return;

      const action = button.getAttribute("data-card-action");
      e.preventDefault();

      if (action === "add") {
        this.handleAdd(cardAction);
      } else if (action === "remove") {
        this.handleRemove(cardAction);
      }
    });
  }

  handleAdd(cardAction) {
    const attributesField = cardAction.closest(".attributes-field");
    if (!attributesField) {
      console.error("CardAction: Could not find parent .attributes-field");
      return;
    }

    const currentGroups = attributesField.querySelectorAll(".variation-attributes");
    if (currentGroups.length === 0) return;

    // Clone the first group
    const newGroup = currentGroups[0].cloneNode(true);

    // Clear values
    this.clearItemValues(newGroup);

    // New index = current number of groups
    const newIndex = currentGroups.length;

    // Update ALL input names in the new group using WORKING pattern
    const inputs = newGroup.querySelectorAll("[name]");
    inputs.forEach((input) => {
      const oldName = input.getAttribute("name");
      // Use pattern 3 that works: /\[attributes\]\[\d+\]/
      const newName = oldName.replace(/\[attributes\]\[\d+\]/, `[attributes][${newIndex}]`);
      input.setAttribute("name", newName);
      console.log(`[ADD] Updated: ${oldName} -> ${newName}`);
    });

    // Insert before card-action
    attributesField.insertBefore(newGroup, cardAction);

    // Reindex all attributes to ensure sequential order
    this.reindexAllAttributes(attributesField);

    // Dispatch event
    const event = new CustomEvent("card-action:item-added", {
      detail: {
        item: newGroup,
        index: newIndex,
        selector: ".variation-attributes",
        target: attributesField
      }
    });
    document.dispatchEvent(event);
  }

  handleRemove(cardAction) {
    const attributesField = cardAction.closest(".attributes-field");
    if (!attributesField) return;

    const groups = attributesField.querySelectorAll(".variation-attributes");

    if (groups.length <= 1) {
      this.clearItemValues(groups[0]);
      // Reset first group index to 0
      const inputs = groups[0].querySelectorAll("[name]");
      inputs.forEach((input) => {
        const oldName = input.getAttribute("name");
        const newName = oldName.replace(/\[attributes\]\[\d+\]/, `[attributes][0]`);
        input.setAttribute("name", newName);
      });
      return;
    }

    // Remove the last group
    const lastGroup = groups[groups.length - 1];
    lastGroup.remove();

    // Reindex remaining attributes
    this.reindexAllAttributes(attributesField);

    const event = new CustomEvent("card-action:item-removed", {
      detail: {
        selector: ".variation-attributes",
        target: attributesField
      }
    });
    document.dispatchEvent(event);
  }

  reindexAllAttributes(attributesField) {
    const groups = attributesField.querySelectorAll(".variation-attributes");
    console.log(`[REINDEX] Reindexing ${groups.length} attribute groups`);

    groups.forEach((group, idx) => {
      const inputs = group.querySelectorAll("[name]");
      inputs.forEach((input) => {
        const oldName = input.getAttribute("name");
        // Use pattern 3 that works
        const newName = oldName.replace(/\[attributes\]\[\d+\]/, `[attributes][${idx}]`);
        if (oldName !== newName) {
          input.setAttribute("name", newName);
          console.log(`[REINDEX] ${oldName} -> ${newName}`);
        }
      });
    });
  }

  clearItemValues(item) {
    const inputs = item.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      if (input.type === "hidden") {
        input.value = "";
      } else if (input.type === "select-one") {
        input.selectedIndex = 0;
        input.value = "";
      } else {
        input.value = "";
      }
      input.classList.remove("is-invalid", "error");
    });
  }
}

export default CardActionHandler;
