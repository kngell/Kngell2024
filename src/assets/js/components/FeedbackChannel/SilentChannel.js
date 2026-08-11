// js/components/FeedbackChannel/SilentChannel.js
import FeedbackChannel from "js/core/Contracts/FeedbackChannelInterface";

export default class SilentChannel extends FeedbackChannel {
  constructor(options = {}) {
    super();
    this.logger = options.logger || console;
    this._messageCount = 0;
  }

  success(message, options = {}) {
    this._log("success", message, options);
  }

  error(message, options = {}) {
    this._log("error", message, options);
  }

  warning(message, options = {}) {
    this._log("warning", message, options);
  }

  info(message, options = {}) {
    this._log("info", message, options);
  }

  clear() {
    // No-op
  }

  destroy() {
    // No-op
  }

  _log(type, message, options) {
    this.logger.debug(`[SilentChannel] ${type}: ${message}`, options);
    this._messageCount++;
  }

  getMessageCount() {
    return this._messageCount;
  }
}
