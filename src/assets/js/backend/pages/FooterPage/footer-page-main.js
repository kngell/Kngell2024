import FooterManager from "js/components/Managers/FooterManager";

const initFooterManager = () => {
  if (!window.footerManagerInstance) {
    // Pass the correct flash selector
    window.footerManagerInstance = new FooterManager({
      flashSelector: ".footer-page__content-bis",
      channelStrategy: "flash",
      notificationContainerId: "app-notifications"
    });
    window.footerManagerInstance.init();
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initFooterManager);
} else {
  initFooterManager();
}

export default FooterManager;
