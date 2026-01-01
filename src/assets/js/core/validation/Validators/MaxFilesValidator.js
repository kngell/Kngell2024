import BaseValidator from "../BaseValidator.js";
import BrowserLogger from "js/utils/logger";
const logger = new BrowserLogger("MaxFilesValidator");

export default class MaxFilesValidator extends BaseValidator {
  validate() {
    logger.debug("🔍 MaxFilesValidator.validate() called:", {
      field: this.field,
      display: this.display,
      value: this.value,
      ruleValue: this.ruleValue,
      valueType: typeof this.value,
      isFileList: this.value instanceof FileList,
      fileCount: this.value?.length || 0,
    });

    if (this.isEmpty(this.value)) {
      logger.debug("✅ MaxFilesValidator: Empty value, skipping");
      return null;
    }

    // Convert FileList to array properly
    const files = this.getFiles();
    const maxFiles = parseInt(this.ruleValue, 10);

    logger.debug("📁 Files analysis:", {
      filesCount: files.length,
      maxFiles: maxFiles,
      fileNames: files.map((f) => f.name),
      files: files,
    });

    if (files.length > maxFiles) {
      const error = this.errorMessage(this.errorParams.message, this.display, maxFiles);
      logger.debug("🚨 MaxFilesValidator: Validation FAILED", {
        error,
        selectedFiles: files.length,
        maxAllowed: maxFiles,
      });
      return error;
    }

    logger.debug("✅ MaxFilesValidator: Validation PASSED");
    return null;
  }

  getFiles() {
    logger.debug("📁 getFiles() input:", {
      value: this.value,
      valueType: typeof this.value,
      isFileList: this.value instanceof FileList,
      isArray: Array.isArray(this.value),
      length: this.value?.length,
    });

    // Handle FileList object
    if (this.value instanceof FileList) {
      const files = Array.from(this.value);
      logger.debug("📁 Converted FileList to array:", {
        fileCount: files.length,
        fileNames: files.map((f) => f.name),
      });
      return files;
    }

    // Handle single File object
    if (this.value instanceof File) {
      logger.debug("📁 Single File object detected:", this.value.name);
      return [this.value];
    }

    // Handle array of files
    if (Array.isArray(this.value)) {
      logger.debug("📁 Array of files detected:", {
        fileCount: this.value.length,
        fileNames: this.value.map((f) => f?.name),
      });
      return this.value;
    }

    logger.debug("❌ Unknown file value type:", {
      type: typeof this.value,
      value: this.value,
    });
    return [];
  }

  isEmpty(value) {
    if (!value) {
      logger.debug("📁 isEmpty: value is falsy");
      return true;
    }
    if (value instanceof FileList && value.length === 0) {
      logger.debug("📁 isEmpty: FileList is empty");
      return true;
    }
    if (Array.isArray(value) && value.length === 0) {
      logger.debug("📁 isEmpty: Array is empty");
      return true;
    }
    if (value instanceof File) {
      logger.debug("📁 isEmpty: Single file exists");
      return false;
    }

    logger.debug("📁 isEmpty: Value exists", {
      type: typeof value,
      length: value?.length,
    });
    return false;
  }
}
