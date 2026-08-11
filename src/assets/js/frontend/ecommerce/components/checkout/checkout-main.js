import CheckoutManager from "js/components/Managers/CheckoutManager";

const initCheckoutManager = () => {
  if (!window.checkoutManagerInstance) {
    window.checkoutManagerInstance = new CheckoutManager({
      flashSelector: ".checkout__body",
      containerSelector: ".checkout__body",
      channelStrategy: "flash",
      notificationContainerId: "checkout-notifications",
      lazyLoadModals: true
    });
    window.checkoutManagerInstance.init();
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initCheckoutManager);
} else {
  initCheckoutManager();
}

export default CheckoutManager;
