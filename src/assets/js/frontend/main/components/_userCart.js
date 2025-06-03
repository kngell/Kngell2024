export default class UserCart {
  constructor() {
    this._init();
  }
  _init = () => {
    this._saveUserCartToLocalStorage();
    this._restoreUserCart();
  };
  _saveUserCartToLocalStorage = () => {
    // Get the 'cart' parameter from the URL
    const params = new URLSearchParams(window.location.search);
    const cart = params.get("cart");
    if (cart) {
      try {
        // Decode and parse the JSON cart data
        const cartData = JSON.parse(decodeURIComponent(cart));
        // Save to localStorage
        localStorage.setItem("cart", JSON.stringify(cartData));
        // Optionally, remove the cart param from the URL for cleanliness
        params.delete("cart");
        window.history.replaceState(
          {},
          "",
          window.location.pathname +
            (params.toString() ? "?" + params.toString() : "")
        );
      } catch (e) {
        console.error("Failed to parse cart data:", e);
      }
    }
  };
  _restoreUserCart = () => {
    const cart = localStorage.getItem("cart");
    console.log(cart);
    if (cart) {
      fetch("/restore-cart", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: cart,
      }).then(() => {
        // Optionally clear localStorage after restoring
        localStorage.removeItem("cart");
      });
    }
  };
}
