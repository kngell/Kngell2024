<?php

declare(strict_types=1);

final readonly class Validator
{
    private array $inputRules;
    private AbstractValidatorCreator $validator;
    private array $inputFields;

    public function validate(array $inputFields, string $rules, ?Model $model = null) : array|bool
    {
        $results = [];
        $this->validatorParams($inputFields, $rules, $model);
        foreach ($this->inputRules as $input => $rules) {
            if (array_key_exists($input, $inputFields)) {
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

    private function validatorParams(array $inputFields, string $ruleFileName, ?Model $model = null) : void
    {
        try {
            $rulesFile = FileManager::get(APP . 'Forms', $ruleFileName . '.yaml');
            $this->inputRules = YamlFile::get($rulesFile);
            $this->validator = ValidatorCreatorFactory::create($ruleFileName, $model, $inputFields);
            $this->inputFields = $inputFields;
        } catch (Throwable $th) {
            //throw $th;
        }
    }
}