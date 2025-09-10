import DashboardManager from "js/backend/components/DashboardManager";
import ProductListCheckboxManager from "js/backend/components/ProducListCheckboxManager";
class Main {
  constructor() {
    this._init();
  }
  _init() {
    new DashboardManager();
    new ProductListCheckboxManager();
    // new SidebarMenu();
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => new Main());
} else {
  new Main();
}
