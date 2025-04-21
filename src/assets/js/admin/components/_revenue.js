import AdminChart from "./_admin_chart";
export default class Revenue extends AdminChart {
  constructor() {
    super();
    this.type = "line";
    this.el = document.querySelector("#admin-revenue");
    this.colors = [
      "rgba(221, 221, 226, 0.25)",
      "rgba(192, 152, 162, 0.35)",
      "rgba(173, 212, 184, 0.45)",
      "rgba(253, 196, 161, 0.55)",
    ];
    this.data = {
      labels: ["Aug", "Sept", "Oct", "Nov", "Dec", "Jan"],
      datasets: [
        {
          label: "Total orders",
          data: [546, 241, 689, 1907, 6532, 679],
          borderWidth: 1,
          backgroundColor: this.colors,
          borderColor: this.colors,
          borderCapStyle: "round",
          borderJoinStyle: "round",
          fill: {
            target: "origin",
          },
        },
        {
          label: "Revenue",
          data: [1678, 1321, 4907, 5672, 3905, 4230],
          borderWidth: 1,
          backgroundColor: this.colors,
          borderColor: this.colors,
          borderCapStyle: "round",
          borderJoinStyle: "round",
          fill: {
            target: "origin",
          },
        },
        {
          label: "Refunded",
          data: [205, 600, 783, 200, 907, 1232],
          borderWidth: 1,
          backgroundColor: this.colors,
          borderColor: this.colors,
          borderCapStyle: "round",
          borderJoinStyle: "round",
          fill: {
            target: "origin",
          },
        },
        {
          label: "Daily Profit",
          data: [321, 212, 1002, 4017, 6732, 2531],
          borderWidth: 1,
          backgroundColor: this.colors,
          borderColor: this.colors,
          borderCapStyle: "round",
          borderJoinStyle: "round",
          fill: {
            target: "origin",
          },
        },
      ],
    };
  }
}
