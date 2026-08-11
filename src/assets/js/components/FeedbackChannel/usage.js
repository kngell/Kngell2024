// FooterManager.js
import { getFlashManager, getFlashChannel } from "js/components/FeedbackChannel/FlashManager";

// Get the singleton instance
const flashManager = getFlashManager({
  flashSelector: options.flashSelector || "body",
  containerClass: "flash-container page-flash",
  position: options.flashPosition || "top",
  durations: {
    success: 5000,
    error: 0,
    warning: 5000,
    info: 4000
  },
  modalDelay: 300
});

// Get the flash channel
this.flash = flashManager.getFlashChannel();
