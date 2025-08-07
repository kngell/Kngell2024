export default class QuantityBoxMirror {
  constructor() {
    this._init();
  }
  _init = () => {
    this._setMirror();
  };
  _setMirror = () => {
    document.querySelectorAll(".quantity-box").forEach((box) => {
      const input = box.querySelector(".quantity-box__input");
      const mirror = box.querySelector(".quantity-box__mirror");
      if (input && mirror) {
        const updateWidth = () => {
          mirror.textContent = input.value || "0";
          const mirrorWidth = mirror.offsetWidth + 2; // Add a small buffer
          input.style.width = `${mirrorWidth}px`;
        };

        input.addEventListener("input", updateWidth);
        updateWidth();
      }
    });
  };
}
