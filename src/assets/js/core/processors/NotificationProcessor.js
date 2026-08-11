import BaseResponseProcessor from "./BaseResponseProcessor";

export default class NotificationProcessor extends BaseResponseProcessor {
  constructor(feedbackChannel, options = {}) {
    super();
    this.feedbackChannel = feedbackChannel;
    this.options = {
      permanentErrors: options.permanentErrors ?? true,
      ...options
    };
  }

  handle(context) {
    const { result } = context;

    // Determine notification type and message
    let type = result.type;
    let message = result.error || result.message;

    // Auto-detect based on success flag
    if (result.success === false && !type) {
      type = "error";
    }
    if (result.success === true && !type) {
      type = "success";
    }

    // Fallback message
    if (!message) {
      message = type === "error" ? "Operation failed" : "Operation completed successfully";
    }

    // Show notification through channel
    if (this.feedbackChannel && this.feedbackChannel[type]) {
      const options = type === "error" && this.options.permanentErrors ? { permanent: true } : {};
      this.feedbackChannel[type](message, options);
    }
  }
}
