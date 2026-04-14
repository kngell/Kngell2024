export default class BaseResponseProcessor {
  canHandle(context) {
    return true;
  }

  process(context) {
    if (this.canHandle(context)) {
      this.handle(context);
    }
  }

  handle(context) {}
}
