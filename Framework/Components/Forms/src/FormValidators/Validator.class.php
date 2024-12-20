<?php

declare(strict_types=1);

final class Validator
{
    private array $inputRules;
    private AbstractValidatorFactory $validator;

    public function __construct(private array $inputFields, string $rules, ?Model $model = null)
    {
        $rulesFile = FileManager::searchFile(APP . 'Forms', $rules . '.yaml');
        $this->inputRules = YamlFile::get($rulesFile);
        $this->validator = new ValidatorFactory($model);
    }

    public function validate() : array|bool
    {
        $results = [];
        foreach ($this->inputRules as $input => $rules) {
            if (array_key_exists($input, $this->inputFields)) {
                $display = $rules['display'];
                unset($rules['display']);
                $results = array_merge($results, $this->runValidator($display, $input, $rules));
            }
        }
        return $results;
    }

    private function runValidator(string $display, string $input, array $rules) : array
    {
        $results = [];
        foreach ($rules as $rule_name => $rule_value) {
            $error = $this->validator->run($rule_name, $display, $this->inputFields[$input], $rule_value);
            if (is_string($error) && ! empty($error)) {
                $results[$input][] = $error;
            }
        }
        return $results;
    }
}