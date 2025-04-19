export default class ExportFiles {
  constructor(el) {
    this.el = el;
    this.btnPdf = el.querySelector("#exportPdf");
    this.btnJson = el.querySelector("#exportJson");
    this.btnCsv = el.querySelector("#exportCsv");
    this.btnXls = el.querySelector("#exportXls");
    this.jsonFilename = "export.json";
    this.excelFilename = "export.xsl";
    this.init();
  }
  init = () => {
    this.btnPdf.addEventListener("click", (e) => this.handleExportPdf(e));
    this.btnJson.addEventListener("click", (e) => this.handleExportJson(e));
    this.btnCsv.addEventListener("click", (e) => this.handleExportCsv(e));
    this.btnXls.addEventListener("click", (e) => this.handleExportXls(e));
  };
  handleExportPdf = (e) => {
    e.preventDefault();
    const new_Windows = window.open("", "", "width=800, height=600, top=0");
    const html_code = `
    <link rel="stylesheet" href="/public/assets/css/training/pratique/table.css">
    <main><div class="table-container">${this.el.innerHTML}</div></main>
    `;
    new_Windows.document.body.innerHTML = html_code;
    setTimeout(() => {
      new_Windows.print();
      new_Windows.close();
    }, 200);
  };
  handleExportJson = (e) => {
    e.preventDefault();
    let tableData = [],
      tableRows = this.el.querySelectorAll(".table__body .table-row");
    const tableHead = this.cleanTableHeader();
    tableRows.forEach((row) => {
      const rowObj = {};
      const t_cells = row.querySelectorAll(".table-data");
      t_cells.forEach((cell, index) => {
        const img = cell.querySelector("img");
        if (img) {
          rowObj["customer img"] = decodeURIComponent(img.src);
        }
        rowObj[tableHead[index]] = cell.textContent.trim();
      });
      tableData.push(rowObj);
    });
    this.download(JSON.stringify(tableData, null, 4), "json", "exportjson");
  };
  handleExportCsv = (e) => {
    e.preventDefault();
    const data = this.exportXslCsvData();
    this.download(data, "csv", "exportJson");
  };
  handleExportXls = (e) => {
    e.preventDefault();
    const data = this.exportXslCsvData();
    this.download(data, "excel");
  };
  exportXslCsvData = () => {
    const headings = this.cleanTableHeader().join("\t") + "\t" + "image url";
    const rowsTb = this.el.querySelectorAll(".table__body .table-row");
    const rowData = [...rowsTb]
      .map((row) => {
        const cells = row.querySelectorAll(".table-heading,.table-data");
        const img = decodeURIComponent(row.querySelector("img").src);

        return (
          [...cells].map((cell) => cell.textContent.trim()).join("\t") +
          "\t" +
          img
        );
      })
      .join("\n");

    return headings + "\n" + rowData;
  };
  download = (content, mime, filename = "") => {
    const mime_types = {
      json: "application/json",
      csv: "text/csv",
      excel:
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    };
    // const link = document.createElement("a");
    // link.download = filename;
    // link.href = `data:${mime_types[mime]};charset=utf-8,${encodeURIComponent(
    //   content
    // )}`;
    // document.body.appendChild(link);
    // link.click();
    // document.body.removeChild(link);

    const blob = new Blob([content], {
      type: `${mime_types[mime]};charset=utf-8`,
    });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };
  cleanTableHeader = () => {
    let tableHead = [];
    const tableHeadings = this.el.querySelectorAll(".table-heading");
    for (let heading of tableHeadings) {
      const heading_text = heading.textContent.trim().split(" ");
      tableHead.push(
        heading_text
          .splice(0, heading_text.length - 1)
          .join(" ")
          .toLowerCase()
      );
      // tableHead.push(
      //   heading_text
      //     .substr(0, heading_text.length - 1)
      //     .trim()
      //     .toLowerCase()
      // );
    }
    return tableHead;
  };
}
