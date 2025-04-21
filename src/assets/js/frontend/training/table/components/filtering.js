export default class Filtering {
  constructor(el) {
    this.el = el;
    this.inputEl = el.querySelector(".search-box__search-input");
    this.tableRows = el.querySelectorAll(".table__body .table-row");
    this.init();
  }
  init = () => {
    this.inputEl.addEventListener("input", (e) => this.handleInput(e));
  };
  handleInput = (e) => {
    e.preventDefault();
    this.tableRows.forEach((row, i) => {
      const tableData = row.textContent.toLowerCase();
      row.classList.toggle(
        "hide",
        tableData.indexOf(e.target.value.toLowerCase()) < 0
      );
      row.style.setProperty("--delay", i / 25 + "s");
    });
    const tableRowsNotHided = this.el.querySelectorAll(
      ".table__body--row:not(.hide)"
    );
    tableRowsNotHided.forEach((row, i) => {
      row.style.backgroundColor = i % 2 == 0 ? "transparent" : "#0000000b";
    });
  };
}
