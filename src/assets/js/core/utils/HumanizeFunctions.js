function looksLikeRawSqlError(msg) {
  if (!msg || typeof msg !== "string") return false;
  return /SQLSTATE$$|Integrity constraint|FOREIGN KEY|Cannot delete or update a parent row|SQL syntax|Duplicate entry|Cannot add or update a child row/i.test(
    msg
  );
}

function humanizeDeletionError({ status, message, displayName = "item" }) {
  const lower = displayName.toLowerCase();

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
    return `This ${lower} can't be deleted because it's still in use by other records.`;
  }
  if (status === 422) {
    return looksLikeRawSqlError(message)
      ? `This ${lower} can't be deleted right now.`
      : message || `This ${lower} can't be deleted.`;
  }
  if (status >= 500) {
    return "Something went wrong on our end. Please try again.";
  }

  if (looksLikeRawSqlError(message)) {
    return `This ${lower} can't be deleted because it's still referenced by other records.`;
  }
  return message || `Failed to delete ${lower}. Please try again.`;
}
