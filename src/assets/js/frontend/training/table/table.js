import Filtering from "./components/filtering";
import Sorting from "./components/sorting";
import ExportFiles from "./components/ExportFiles";
export default class Table {
  constructor(el) {
    this.el = el;
    this.init();
  }
  init = () => {
    //Filtering Table
    new Filtering(this.el);

    // Sorting data
    new Sorting(this.el);

    // Export Files
    new ExportFiles(this.el);
  };
}
