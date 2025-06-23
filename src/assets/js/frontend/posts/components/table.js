import Filtering from "./_filtering";
import Sorting from "./_sorting";
import ExportFiles from "./_exportFiles";
import "../../../../img/excel.png";
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
