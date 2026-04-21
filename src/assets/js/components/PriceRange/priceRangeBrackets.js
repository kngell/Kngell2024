import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("PriceRangeBrackets");

export default class PriceRangeBrackets {
  constructor(options = {}) {
    this.options = {
      containerSelector: '[data-brackets-container="true"]',
      preserveValuesOnReindex: false,
      ...options
    };

    // Try both selectors for flexibility
    this.container =
      document.querySelector(this.options.containerSelector) ||
      document.querySelector(".bracket-range");

    if (!this.container) {
      logger.debug("Container not found, skipping initialization");
      return;
    }

    this.bracketsList =
      this.container.querySelector('[data-brackets-list="true"]') || this.container;
    this.bracketCount = this.container.querySelectorAll(".bracket-range__card").length;

    this.init();
  }

  init() {
    this.attachEventListeners();
    this.updateBracketIndices();
    logger.debug(`Initialized with ${this.bracketCount} bracket(s)`);
  }

  getTemplateCard() {
    // Always get the first card as template
    return this.container.querySelector(".bracket-range__card");
  }

  getAllCards() {
    return this.container.querySelectorAll(".bracket-range__card");
  }

  attachEventListeners() {
    // Use event delegation on the container
    this.container.addEventListener("click", (e) => {
      // Add button
      const addBtn = e.target.closest(".card-action__add-btn, [data-add-bracket]");
      if (addBtn) {
        e.preventDefault();
        const currentCard = addBtn.closest(".bracket-range__card");
        this.addBracket(currentCard);
        return;
      }

      // Remove button
      const removeBtn = e.target.closest(".card-action__remove-btn, [data-remove-card]");
      if (removeBtn) {
        e.preventDefault();
        const card = removeBtn.closest(".bracket-range__card");
        if (card && this.getAllCards().length > 1) {
          this.removeBracket(card);
        }
        return;
      }
    });
  }

  addBracket(sourceCard) {
    const templateCard = this.getTemplateCard();
    if (!templateCard) return;

    const newIndex = this.getAllCards().length;
    const newDisplayIndex = newIndex + 1;

    // Clone template card
    const newCard = templateCard.cloneNode(true);

    // Clear input values and update indices
    this.normalizeCard(newCard, newIndex, newDisplayIndex);

    // Insert after the source card (or append to end)
    if (sourceCard && sourceCard.parentNode) {
      sourceCard.parentNode.insertBefore(newCard, sourceCard.nextSibling);
    } else {
      this.bracketsList.appendChild(newCard);
    }

    // Reindex all brackets
    this.updateBracketIndices();

    logger.debug(`Added bracket ${newDisplayIndex}, total: ${this.getAllCards().length}`);
  }

  removeBracket(card) {
    card.remove();
    this.updateBracketIndices();
    logger.debug(`Removed bracket, remaining: ${this.getAllCards().length}`);
  }

  normalizeCard(card, newIndex, newDisplayIndex) {
    // Update data attribute
    card.setAttribute("data-bracket-index", newIndex);

    // Update title
    const title = card.querySelector(".card-title");
    if (title) {
      title.textContent = `Bracket ${newDisplayIndex}`;
    }

    // Update all inputs
    const inputs = card.querySelectorAll("input");
    inputs.forEach((input) => {
      // Clear value for new bracket
      input.value = "";

      // Update name attribute (replace [0] or [x] with [newIndex])
      const currentName = input.getAttribute("name");
      if (currentName) {
        const newName = currentName.replace(/\[\d+\]/, `[${newIndex}]`);
        input.setAttribute("name", newName);
      }

      // Generate new unique ID (avoid duplicates)
      const currentId = input.getAttribute("id");
      if (currentId) {
        // Remove old ID pattern and create new unique ID
        const baseId = currentId.replace(/_\d+$/, "");
        const newId = `${baseId}_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
        input.setAttribute("id", newId);

        // Update associated label
        const label = card.querySelector(`label[for="${currentId}"]`);
        if (label) {
          label.setAttribute("for", newId);
        }
      }
    });
  }

  updateBracketIndices() {
    const cards = this.getAllCards();

    cards.forEach((card, idx) => {
      const newIndex = idx;
      const newDisplayIndex = idx + 1;

      // Update data attribute
      card.setAttribute("data-bracket-index", newIndex);

      // Update title
      const title = card.querySelector(".card-title");
      if (title) {
        title.textContent = cards.length === 1 ? "Bracket" : `Bracket ${newDisplayIndex}`;
      }

      // Update all inputs (names only, preserve values)
      const inputs = card.querySelectorAll("input");
      inputs.forEach((input) => {
        const currentName = input.getAttribute("name");
        if (currentName) {
          const newName = currentName.replace(/\[\d+\]/, `[${newIndex}]`);
          input.setAttribute("name", newName);
        }

        // Only update IDs if needed (keep existing values)
        const currentId = input.getAttribute("id");
        if (currentId && !this.options.preserveValuesOnReindex) {
          const newId = currentId.replace(/-\d+-/, `-${newIndex}-`);
          input.setAttribute("id", newId);

          const label = card.querySelector(`label[for="${currentId}"]`);
          if (label) {
            label.setAttribute("for", newId);
          }
        }
      });
    });

    logger.debug(`Reindexed ${cards.length} bracket(s)`);
  }

  reset() {
    // Remove all brackets except the first
    const cards = this.getAllCards();
    for (let i = cards.length - 1; i > 0; i--) {
      cards[i].remove();
    }

    // Reset the first card
    const firstCard = this.getTemplateCard();
    if (firstCard) {
      // Clear all inputs
      const inputs = firstCard.querySelectorAll("input");
      inputs.forEach((input) => {
        input.value = "";

        // Reset name to index 0
        const currentName = input.getAttribute("name");
        if (currentName) {
          const defaultName = currentName.replace(/\[\d+\]/, `[0]`);
          input.setAttribute("name", defaultName);
        }
      });

      // Reset title
      const title = firstCard.querySelector(".card-title");
      if (title) {
        title.textContent = "Bracket";
      }

      // Reset data attribute
      firstCard.setAttribute("data-bracket-index", "0");
    }

    this.updateBracketIndices();
    logger.debug("Price range brackets reset to initial state");
  }

  destroy() {
    // Remove event listeners (if bound)
    if (this.boundClickHandler) {
      this.container.removeEventListener("click", this.boundClickHandler);
    }

    // Clear references
    this.container = null;
    this.bracketsList = null;
    logger.debug("PriceRangeBrackets destroyed");
  }
}
