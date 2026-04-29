import TableManager from "js/components/List/TableManager";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("HeroTableMain");

class HeroTableMain {
  constructor() {
    this.config = {
      tableType: "hero", // Lowercase to match data-table-type
      entityName: "hero",
      entityDisplayName: "Hero",
      entityNamePlural: "heroes",
      idAttribute: "data-hero-id",
      checkboxName: "heroes[]",
      emptyStateConfig: {
        title: "No heroes found",
        message: "Get started by adding your first hero",
        actionUrl: "/hero-page/add",
        actionText: "Add Hero"
      }
    };
  }

  init() {
    const table = document.querySelector('.table[data-table-type="hero"]');
    if (table && !window.__heroTableManager) {
      logger.debug("Initializing HeroTableMain");
      window.__heroTableManager = new TableManager(this.config);
    }
  }
}

// Auto-initialize only on hero page - FIXED: check for hero, not products
if (document.querySelector('.table[data-table-type="hero"]')) {
  const heroTable = new HeroTableMain();
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => heroTable.init());
  } else {
    heroTable.init();
  }
}

export default HeroTableMain;
