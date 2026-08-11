/**
 * Heuristic detector for raw SQL/database error messages leaking from the server.
 * Matches: SQLSTATE[…], "Integrity constraint violation", "FOREIGN KEY", "Cannot delete or update a parent row", etc.
 */
export const looksLikeRawSqlError = (msg) => {
  if (!msg || typeof msg !== "string") return false;
  return /SQLSTATE$$|Integrity constraint|FOREIGN KEY|Cannot delete or update a parent row|SQL syntax|Duplicate entry|Cannot add or update a child row/i.test(
    msg
  );
};

/**
 * Translate a raw deletion error into a friendly user message based on HTTP status.
 * Strips out leaking SQL details.
 */
export const humanizeDeletionError = ({ status, message, displayName = "item" }) => {
  const lower = displayName.toLowerCase();

  // Network / timeout / unknown — no status
  if (status == null) {
    if (looksLikeRawSqlError(message)) {
      return `This ${lower} can't be deleted because it's still referenced by other records.`;
    }
    return message || "Couldn't reach the server. Please check your connection and try again.";
  }

  if (status === 401 || status === 403) {
    return "You don't have permission to perform this action.";
  }
  if (status === 404) {
    return `This ${lower} no longer exists. The list will refresh.`;
  }
  if (status === 409) {
    // FK constraint, in-use, etc.
    return `This ${lower} can't be deleted because it's still in use by other records.`;
  }
  if (status === 422) {
    // Validation — the server message is usually safe
    return looksLikeRawSqlError(message)
      ? `This ${lower} can't be deleted right now.`
      : message || `This ${lower} can't be deleted.`;
  }
  if (status >= 500) {
    return "Something went wrong on our end. Please try again.";
  }

  // 4xx fallback — humanize SQL leaks, otherwise pass message through
  if (looksLikeRawSqlError(message)) {
    return `This ${lower} can't be deleted because it's still referenced by other records.`;
  }
  return message || `Failed to delete ${lower}. Please try again.`;
};
