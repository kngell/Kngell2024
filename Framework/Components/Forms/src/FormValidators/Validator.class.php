<?php

declare(strict_types=1);

final class Validator
{
    protected array $validators = [];
    private array $inputRules;

    public function __construct(private array $inputFields)
    {
        $rulesFile = FileManager::searchFile(APP . 'Forms', 'productFormRules.yaml');
        $this->inputRules = YamlFile::get($rulesFile);
    }

    public function validate() : array
    {
        foreach ($this->inputRules as $input => $rules) {
            if (array_key_exists($input, $this->inputFields)) {
                $display = $rules['display'];
                unset($rules['display']);
                foreach ($rules as $rule_name => $rule_value) {
                    $this->add($input, ValidatorFactory::create($rule_name, $display, $this->inputFields[$input], $rule_value));
                }
            }
        }
        return $this->runValidator();
    }

    private function runValidator() : array
    {
        $results = [];
        foreach ($this->validators as $input => $validators) {
            /** @var ValidatorInterface[] $validators */
            foreach ($validators as $validator) {
                if ($result = $validator->validate()) {
                    $results[$input][] = $result;
                }
            }
        }
        return $results;
    }

    private function add(string $inputName, ValidatorInterface $component): void
    {
        $this->validators[$inputName][] = $component;
    }
}