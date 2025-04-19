export default class Sorting {
  constructor(el) {
    this.el = el;
    this.tableHeadings = el.querySelectorAll(".table__head .table-heading");
    this.tableRows = el.querySelectorAll(".table__body .table-row");
    this.init();
  }
  init = () => {
    this.handleSorting();
  };
  handleSorting = () => {
    this.tableHeadings.forEach((heading, i) => {
      let sort_asc = true;
      heading.addEventListener("click", (e) => {
        e.preventDefault();
        this.tableHeadings.forEach((heading) => {
          heading.classList.remove("active");
        });
        heading.classList.add("active");
        this.el
          .querySelectorAll("td")
          .forEach((td) => td.classList.remove("active"));
        this.tableRows.forEach((row) => {
          row.querySelectorAll("td")[i].classList.add("active");
        });
        heading.classList.toggle("asc", sort_asc);
        sort_asc = heading.classList.contains("asc") ? false : true;
        this.sortTable(i, sort_asc);
      });
    });
  };
  sortTable = (col, sort_asc) => {
    [...this.tableRows]
      .sort((a, b) => {
        const row1 = a.querySelectorAll("td")[col].textContent.toLowerCase();
        const row2 = b.querySelectorAll("td")[col].textContent.toLowerCase();
        return sort_asc ? (row1 < row2 ? -1 : +1) : row1 > row2 ? -1 : +1; // for descending order;
      })
      .map((sortedRow) =>
        this.el.querySelector(`.table__body`).appendChild(sortedRow)
      );
  };
}
