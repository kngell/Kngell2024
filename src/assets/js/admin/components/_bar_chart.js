import AdminChart from "./_admin_chart";

export default class BarChart extends AdminChart {
  constructor() {
    super();
    this.type = "bar";
    this.el = document.querySelector("#admin-project");
    this.colors = ["#dddde2", "#add4b8", "#bdbfff", "#c098a2"];
    this.data = {
      labels: ["Sept - Oct", "Nov - Dec", "Jan - Feb"],
      datasets: [
        {
          label: "Total",
          data: [125, 200, 576],
          borderWidth: 1,
          backgroundColor: this.colors[0],
          borderColor: this.colors[0],
        },
        {
          label: "Completed",
          data: [120, 185, 243],
          borderWidth: 1,
          backgroundColor: this.colors[1],
          borderColor: this.colors[1],
        },
        {
          label: "In Progress",
          data: [5, 10, 102],
          borderWidth: 1,
          backgroundColor: this.colors[2],
          borderColor: this.colors[2],
        },
        {
          label: "Delayed/Canceled",
          data: [0, 5, 231],
          borderWidth: 1,
          backgroundColor: this.colors[3],
          borderColor: this.colors[3],
        },
      ],
    };
  }
}
