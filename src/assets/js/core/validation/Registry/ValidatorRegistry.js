// js/core/validation/factory/ValidatorRegistry.js
import RequiredValidator from "js/core/validation/Validators/RequiredValidator";
import MinValidator from "js/core/validation/Validators/MinValidator";
import MaxValidator from "js/core/validation/Validators/MaxValidator";
import PatternValidator from "js/core/validation/Validators/PatternValidator";
import NumericValidator from "js/core/validation/Validators/NumericValidator";
import RequiredIfValidator from "js/core/validation/Validators/RequiredIfValidator";
import MinValueValidator from "js/core/validation/Validators/MinValueValidator";
import MaxValueValidator from "js/core/validation/Validators/MaxValueValidator";
import LteValidator from "js/core/validation/Validators/LteValidator";
import GteValidator from "js/core/validation/Validators/GteValidator";
import ArrayValidator from "js/core/validation/Validators/ArrayValidator";
import MaxItemsValidator from "js/core/validation/Validators/MaxItemsValidator";
import ItemsValidator from "js/core/validation/Validators/ItemsValidator";
import FileSizeValidator from "js/core/validation/Validators/FileSizeValidator";
import UploadLimitValidator from "js/core/validation/Validators/UploadLimitValidator";
import PostLimitValidator from "js/core/validation/Validators/PostLimitValidator";
import MaxFilesValidator from "js/core/validation/Validators/MaxFilesValidator";
import MimesValidator from "js/core/validation/Validators/MimesValidator";

export default class ValidatorRegistry {
  static validators = {
    required: RequiredValidator,
    min: MinValidator,
    max: MaxValidator,
    pattern: PatternValidator,
    numeric: NumericValidator,
    required_if: RequiredIfValidator,
    min_value: MinValueValidator,
    max_value: MaxValueValidator,
    lte: LteValidator,
    gte: GteValidator,
    array: ArrayValidator,
    max_items: MaxItemsValidator,
    items: ItemsValidator,
    file_size: FileSizeValidator,
    upload_limit: UploadLimitValidator,
    post_limit: PostLimitValidator,
    max_files: MaxFilesValidator,
    mimes: MimesValidator,
  };

  static getValidator(ruleName) {
    return this.validators[ruleName] || null;
  }

  static registerValidator(ruleName, validatorClass) {
    this.validators[ruleName] = validatorClass;
  }

  static hasValidator(ruleName) {
    return ruleName in this.validators;
  }

  static getAllValidatorNames() {
    return Object.keys(this.validators);
  }
}
