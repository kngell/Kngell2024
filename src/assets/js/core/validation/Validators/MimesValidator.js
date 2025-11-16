import BaseValidator from "../BaseValidator.js";
import BrowserLogger from "js/utils/logger";
const logger = new BrowserLogger("MimesValidator");

export default class MimesValidator extends BaseValidator {
  validate() {
    logger.debug("🔍 MimesValidator DEBUG:", {
      field: this.field,
      display: this.display,
      value: this.value,
      ruleValue: this.ruleValue,
      errorParams: this.errorParams,
    });

    if (this.isEmpty(this.value)) {
      logger.debug("✅ MimesValidator: Empty value, skipping");
      return null;
    }

    const files = this.getFiles();
    const allowedTypes = this.ruleValue.split(",").map((type) => type.trim());

    logger.debug("📁 Files to validate:", files);
    logger.debug("✅ Allowed types:", allowedTypes);

    for (const file of files) {
      if (!file || !file.type) {
        logger.debug("❌ Skipping invalid file:", file);
        continue;
      }

      logger.debug("🔎 Checking file:", {
        name: file.name,
        type: file.type,
        extension: this.getFileExtension(file.name),
      });

      if (!this.isFileTypeAllowed(file, allowedTypes)) {
        const error = this.errorMessage(
          this.errorParams.message,
          this.display,
          file.name,
          this.getFileExtension(file.name),
          this.ruleValue,
        );

        logger.debug("🚨 MimesValidator: Validation FAILED", {
          error: error,
          file: file.name,
          allowedTypes: this.ruleValue,
        });
        return error;
      }
    }

    logger.debug("✅ MimesValidator: Validation PASSED");
    return null;
  }

  isFileTypeAllowed(file, allowedTypes) {
    const fileExtension = this.getFileExtension(file.name).toLowerCase();
    const fileType = file.type.toLowerCase();

    for (const allowedType of allowedTypes) {
      // Check if it's a main type (e.g., 'image', 'video')
      if (this.isMainType(allowedType)) {
        if (fileType.startsWith(`${allowedType}/`)) {
          return true;
        }
      }
      // Check specific MIME type (e.g., 'image/jpeg')
      else if (allowedType.includes("/")) {
        if (fileType === allowedType.toLowerCase()) {
          return true;
        }
      }
      // Check file extension (e.g., 'jpg', 'pdf')
      else {
        if (fileExtension === allowedType.toLowerCase()) {
          return true;
        }

        // Check if extension maps to allowed MIME type
        const mimeTypesForExtension = this.getMimeTypesForExtension(fileExtension);
        if (mimeTypesForExtension.includes(allowedType.toLowerCase())) {
          return true;
        }
      }
    }

    return false;
  }

  isMainType(type) {
    const mainTypes = ["image", "video", "audio", "text", "application", "font", "model"];
    return mainTypes.includes(type.toLowerCase());
  }

  getFileExtension(filename) {
    return filename.slice(((filename.lastIndexOf(".") - 1) >>> 0) + 2);
  }

  getMimeTypesForExtension(extension) {
    // Simplified MIME type mapping - you can expand this based on your PHP MimeTypeConstants
    const mimeMap = {
      // Images
      jpg: ["image/jpeg"],
      jpeg: ["image/jpeg"],
      png: ["image/png"],
      gif: ["image/gif"],
      webp: ["image/webp"],
      bmp: ["image/bmp"],
      tiff: ["image/tiff"],

      // Documents
      pdf: ["application/pdf"],
      doc: ["application/msword"],
      docx: ["application/vnd.openxmlformats-officedocument.wordprocessingml.document"],
      xls: ["application/vnd.ms-excel"],
      xlsx: ["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"],
      ppt: ["application/vnd.ms-powerpoint"],
      pptx: ["application/vnd.openxmlformats-officedocument.presentationml.presentation"],

      // Archives
      zip: ["application/zip"],
      rar: ["application/vnd.rar"],
      "7z": ["application/x-7z-compressed"],

      // Text
      txt: ["text/plain"],
      csv: ["text/csv"],

      // Audio
      mp3: ["audio/mpeg"],
      wav: ["audio/wav"],
      ogg: ["audio/ogg"],

      // Video
      mp4: ["video/mp4"],
      avi: ["video/x-msvideo"],
      mov: ["video/quicktime"],
      webm: ["video/webm"],
    };

    return mimeMap[extension.toLowerCase()] || [];
  }

  getFiles() {
    // Handle FileList object
    if (this.value instanceof FileList) {
      return Array.from(this.value);
    }

    // Handle single File object
    if (this.value instanceof File) {
      return [this.value];
    }

    // Handle array of files
    if (Array.isArray(this.value)) {
      return this.value;
    }

    // Handle case where value is already an array of FileList (shouldn't happen but just in case)
    if (Array.isArray(this.value) && this.value[0] instanceof FileList) {
      return Array.from(this.value[0]);
    }

    logger.debug("❌ Unknown file value type:", typeof this.value, this.value);
    return [];
  }
}
