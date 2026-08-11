import TableManager from "js/components/List/TableManager";
import BrowserLogger from "js/core/utils/BrowserLogger";

/**
 * Auto-discovers `.table[data-table-type]` elements and instantiates a
 * TableManager per element.
 *
 * Multi-table safe: tracks instances in a WeakMap keyed by the table element
 * (rather than a global `window[...]` flag keyed by tableType, which would
 * collide if two tables of the same type were rendered on the same page).
 */
class TableList {
  static _instances = new WeakMap();

  static initFromDOM() {
    const tables = document.querySelectorAll(".table[data-table-type]");

    tables.forEach((table) => {
      if (TableList._instances.has(table)) return;
      // Also honor a DOM marker so server-side re-renders don't double-init
      if (table.dataset.tableInitialized === "true") return;

      const config = TableList.buildConfig(table);
      const logger = new BrowserLogger(`${config.entityDisplayName}TableMain`);

      logger.debug(`Initializing ${config.entityDisplayName} table`);

      const manager = new TableManager(config, { tableElement: table });
      TableList._instances.set(table, manager);
      table.dataset.tableInitialized = "true";
    });
  }

  static buildConfig(table) {
    const tableType = table.dataset.tableType;
    const entityName = table.dataset.entityName || tableType;
    const entityDisplayName =
      table.dataset.entityDisplayName || entityName.charAt(0).toUpperCase() + entityName.slice(1);
    const entityNamePlural = table.dataset.entityNamePlural || `${entityName}s`;

    return {
      tableType,
      entityName,
      entityDisplayName,
      entityNamePlural,
      idAttribute: `data-${entityName}-id`,
      checkboxName: `${entityNamePlural}[]`,
      emptyStateConfig: {
        title: table.dataset.emptyTitle || `No ${entityNamePlural} found`,
        message: table.dataset.emptyMessage || `Get started by adding your first ${entityName}`,
        actionUrl: table.dataset.emptyActionUrl || "#",
        actionText: table.dataset.emptyActionText || `Add ${entityDisplayName}`
      }
    };
  }

  /**
   * Retrieve the TableManager bound to a specific table element (for tests / debugging).
   */
  static getManager(tableElement) {
    return TableList._instances.get(tableElement) || null;
  }
}

// Auto-init
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => TableList.initFromDOM());
} else {
  TableList.initFromDOM();
}

export default TableList;
