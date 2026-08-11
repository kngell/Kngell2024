/**
 * Abstract interface for delivering feedback messages to the user.
 * Implementations: NotificationChannel (toasts), TableFlashChannel (inline), SilentChannel (no-op).
 *
 * All methods accept (message, options) where options is implementation-specific.
 */
export default class FeedbackChannelInterface {
  success(message, options = {}) {
    throw new Error("Not implemented");
  }
  error(message, options = {}) {
    throw new Error("Not implemented");
  }
  warning(message, options = {}) {
    throw new Error("Not implemented");
  }
  info(message, options = {}) {
    throw new Error("Not implemented");
  }

  /** Optional cleanup */
  destroy() {}
}
