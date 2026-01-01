<?php

declare(strict_types=1);

class ProductValidatorCreator extends AbstractValidatorCreator
{
    public function __construct(
        private ?Model $model,
        private array $inputFields,
        private ValidationConfig $config,
    ) {
    }

    public function create(
        string $ruleName,
        string $display,
        mixed $inputValue,
        mixed $ruleValue,
        string $fieldName,
    ): ?AbstractValidator {
        $errorParams = [
            'message' => $this->config->getMessage($ruleName),
            'classes' => $this->config->getHintClasses(),
        ];

        // First check for custom rules
        if ($this->hasCustomRule($ruleName)) {
            return new CustomRuleValidator($ruleName, $display, $inputValue, $ruleValue, $this);
        }

        if ($this->isSimpleRule($ruleName)) {
            return new SimpleRuleValidator($errorParams, $display, $inputValue, $ruleName, $ruleValue);
        }

        return match ($ruleName) {
            // Basic validators
            'required' => new RequiredValidator($errorParams, $display, $inputValue),
            'min' => new MinValidator($errorParams, $display, $inputValue, $ruleValue),
            'max' => new MaxValidator($errorParams, $display, $inputValue, $ruleValue),
            'pattern' => new PatternValidator($errorParams, $display, $inputValue, $ruleValue),
            'numeric' => new NumericValidator($errorParams, $display, $inputValue, $ruleValue),
            'required_if' => new RequiredIfValidator($errorParams, $display, $inputValue, $ruleValue, $this->inputFields),

            // Number validators
            'min_value' => new MinValueValidator($errorParams, $display, $inputValue, $ruleValue),
            'max_value' => new MaxValueValidator($errorParams, $display, $inputValue, $ruleValue),

            // Array validators (for variations)
            'array' => new ArrayValidator($errorParams, $display, $inputValue, $ruleValue),
            'max_items' => new MaxItemsValidator($errorParams, $display, $inputValue, $ruleValue),
            'items' => new ItemsValidator($errorParams, $display, $inputValue, $ruleValue, $this, $fieldName), // Inject $this

            // Selection validators
            'in' => new InValidator($errorParams, $display, $inputValue, $ruleValue),

            // Comparison validators (if needed)
            'lte' => new LteValidator($errorParams, $display, $inputValue, $ruleValue, $this->inputFields),
            'gte' => new GteValidator($errorParams, $display, $inputValue, $ruleValue),
            'mimes' => new MimesValidator($errorParams, $display, $inputValue, $ruleValue),
            'upload_error' => new UploadErrorValidator($errorParams, $display, $inputValue, $ruleValue),
            'file_size' => new FileSizeValidator($errorParams, $display, $inputValue, $ruleValue),
            'post_limit' => new PostLimitValidator($errorParams, $display, $inputValue, $ruleValue),
            'max_files' => new MaxFilesValidator($errorParams, $display, $inputValue, $ruleValue),
            'upload_limit' => new UploadLimitValidator($errorParams, $display, $inputValue, $ruleValue),
            'unique' => new UniqueValidator($errorParams, $display, $inputValue, $ruleValue, $this->model, $this->inputFields, $fieldName),
            default => throw new InvalidArgumentException("Unknown validation rule: $ruleName")
        };
    }
}