import BaseResponseProcessor from "./BaseResponseProcessor";

export default class RedirectProcessor extends BaseResponseProcessor {
  constructor(options = {}) {
    super();
    this.delays = {
      info: 2000,
      warning: 2500,
      success: 1500,
      error: 1000,
      danger: 1000,
      ...options.delays,
    };
  }

  handle(context) {
    const { result } = context;

    if (result.redirect) {
      context.redirectUrl = result.redirect;
      context.redirectDelay = this.getDelay(result.type);

      if (result.type === "danger" || result.type === "error") {
        context.shouldRedirect = false;
      }
    }
  }

  getDelay(type) {
    return this.delays[type] || 1500;
  }
}
