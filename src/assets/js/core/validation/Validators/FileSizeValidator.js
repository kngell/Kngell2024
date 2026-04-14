import BaseValidator from "../BaseValidator";

export default class FileSizeValidator extends BaseValidator {
  validate() {
    if (this.isEmpty(this.value)) return null;

    const files = Array.isArray(this.value) ? this.value : [this.value];
    const maxSize = this.parseSize(this.ruleValue);

    for (const file of files) {
      if (!file || !file.size) continue;

      if (file.size > maxSize) {
        return this.errorMessage(
          this.errorParams.message,
          this.display,
          this.formatBytes(file.size),
          this.ruleValue,
        );
      }
    }

    return null;
  }

  parseSize(size) {
    const units = { B: 1, K: 1024, M: 1048576, G: 1073741824 };
    const match = size
      .toString()
      .toUpperCase()
      .match(/^(\d+(?:\.\d+)?)\s*([BKMGT]?)?$/);
    return match ? parseFloat(match[1]) * (units[match[2] || "B"] || 1) : 0;
  }

  formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
  }
}
