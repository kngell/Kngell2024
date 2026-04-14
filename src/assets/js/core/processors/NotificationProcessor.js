import BaseResponseProcessor from "./BaseResponseProcessor";

export default class NotificationProcessor extends BaseResponseProcessor {
  constructor(notificationHelper, options = {}) {
    super();
    this.notificationHelper = notificationHelper;
    this.options = options;
  }

  handle(context) {
    const { result } = context;

    const type = result.type || (result.success === false ? "error" : "success");
    const message = result.error || result.message || this.getDefaultMessage(result.success);

    const isPermanent = result.success === false && this.options.permanentErrors;

    if (this.notificationHelper) {
      if (result.success === false) {
        this.notificationHelper.closeAll();
      }

      this.notificationHelper.show(message, type, {
        permanent: isPermanent,
      });
    }
  }
  getDefaultMessage(success) {
    return success ? "Operation completed successfully" : "Operation failed";
  }
}
