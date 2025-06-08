<?php

declare(strict_types=1);

/**
 * Date validator with multiple format support and range validation.
 */
class DateValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s field must be a valid date';
    private const string ERROR_MESSAGE_FORMAT = 'The %s field must be in format %s';
    private const string ERROR_MESSAGE_RANGE = 'The %s field must be between %s and %s';

    private const array SUPPORTED_FORMATS = [
        'Y-m-d' => 'YYYY-MM-DD',
        'd/m/Y' => 'DD/MM/YYYY',
        'm/d/Y' => 'MM/DD/YYYY',
        'd-m-Y' => 'DD-MM-YYYY',
        'Y-m-d H:i:s' => 'YYYY-MM-DD HH:MM:SS',
    ];

    public function __construct(
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue
    ) {
    }

    public function validate(): string|bool
    {
        if (empty($this->inputValue)) {
            return false; // Let RequiredValidator handle empty values
        }

        if (! is_string($this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }

        $config = $this->parseRuleValue();
        $format = $config['format'] ?? 'Y-m-d';

        $date = $this->parseDate($this->inputValue, $format);
        if (! $date) {
            $formatDisplay = self::SUPPORTED_FORMATS[$format] ?? $format;
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE_FORMAT, $this->display, $formatDisplay));
        }

        if (! $this->isDateInRange($date, $config)) {
            return $this->erroMessage(sprintf(
                self::ERROR_MESSAGE_RANGE,
                $this->display,
                $config['min'] ?? 'no limit',
                $config['max'] ?? 'no limit'
            ));
        }

        return false;
    }

    private function parseRuleValue(): array
    {
        if (is_string($this->ruleValue)) {
            return ['format' => $this->ruleValue];
        }

        if (is_array($this->ruleValue)) {
            return $this->ruleValue;
        }

        return [];
    }

    private function parseDate(string $dateString, string $format): ?DateTime
    {
        $date = DateTime::createFromFormat($format, $dateString);

        if (! $date || $date->format($format) !== $dateString) {
            return null;
        }

        return $date;
    }

    private function isDateInRange(DateTime $date, array $config): bool
    {
        if (isset($config['min'])) {
            $minDate = new DateTime($config['min']);
            if ($date < $minDate) {
                return false;
            }
        }

        if (isset($config['max'])) {
            $maxDate = new DateTime($config['max']);
            if ($date > $maxDate) {
                return false;
            }
        }

        return true;
    }
}