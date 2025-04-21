import Chart from "chart.js/auto";
export default class AdminChart {
  //   constructor(el, data) {
  //     this.el = el;
  //     this.data = data;
  //   }
  update() {
    if (this.el) {
      new Chart(this.el, {
        type: this.type,
        data: this.data,
        options: {
          plugins: {
            legend: { display: false },
          },
        },
      });
    }
  }
}
