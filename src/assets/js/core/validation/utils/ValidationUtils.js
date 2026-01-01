export class ValidationUtils {
  static isEmpty(value) {
    if (value === null || value === undefined || value === "" || value === "[]") {
      return true;
    }
    if (Array.isArray(value) && value.length === 0) {
      return true;
    }
    if (typeof value === "string" && value.trim() === "") {
      return true;
    }
    if (value instanceof FileList && value.length === 0) {
      return true;
    }
    return false;
  }

  static isFileList(value) {
    return value instanceof FileList;
  }

  static isArray(value) {
    return Array.isArray(value);
  }

  static getFileCount(value) {
    if (value instanceof FileList) {
      return value.length;
    }
    if (Array.isArray(value)) {
      return value.length;
    }
    return 0;
  }
}
