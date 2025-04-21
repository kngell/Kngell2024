import AdminChart from "./_admin_chart";

export default class DoughnutChart extends AdminChart {
  constructor() {
    super();
    this.type = "doughnut";
    this.el = document.querySelector("#admin-earnings");
    this.colors = ["#dddde2", "#add4b8", "#bdbfff", "#c098a2"];
    this.data = {
      labels: [
        "Web / Mobile Applications",
        "Branding / Marketing",
        "Finantial Analysis / Web / Mobile",
        "Consultation",
      ],
      datasets: [
        {
          data: [13233, 6234, 10423, 11423],
          borderWidth: 1,
          backgroundColor: this.colors,
          borderColor: this.colors,
        },
      ],
    };
  }
}
