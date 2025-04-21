import Dropdown from "./components/_dropdown";
import Sidebar from "./components/_sidebar";
import ProgressBar from "./components/_progress_bar";
import BarChart from "./components/_bar_chart";
import DoughnutChart from "./components/_doughnutChart";
import SubscriptionReminderModal from "./components/_subscription_reminder_modal";
import Revenue from "./components/_revenue";

class Main {
  constructor() {
    this._init();
  }
  _init() {
    //Dropdown
    new Dropdown().handle();
    // Sidebar
    new Sidebar().handle();
    // Proress bar
    new ProgressBar().update();
    // Charts
    new BarChart().update();
    new DoughnutChart().update();
    // Subscription Reminder Modal
    new SubscriptionReminderModal();
    //Revenue
    new Revenue().update();
  }
}

if (document.readyState !== "loading") {
  new Main();
} else {
  document.addEventListener("DOMContentLoaded", () => {
    new Main();
  });
}
