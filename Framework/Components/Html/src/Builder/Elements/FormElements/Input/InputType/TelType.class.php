<?php

declare(strict_types=1);

class TelType extends AbstractInput
{
    protected const string TYPE = 'tel';

    public function withPhoneValidation(): static
    {
        $this->custom = array_merge($this->custom, [
            'pattern' => '[\+\d\s\-\(\)]{7,20}',
            'maxlength' => '20',
            'autocomplete' => 'tel',
        ]);
        return $this;
    }

    public function withCountryCode(string $countryCode): static
    {
        $this->custom = array_merge($this->custom, [
            'data-country-code' => $countryCode,
        ]);
        return $this;
    }

    public function withInternationalFormat(): static
    {
        $this->custom = array_merge($this->custom, [
            'placeholder' => '+1 (555) 000-0000',
            'data-intl-phone' => 'true',
        ]);
        return $this;
    }

    public function withExtension(): static
    {
        $this->custom = array_merge($this->custom, [
            'data-allow-extension' => 'true',
        ]);
        return $this;
    }

    public function withDefaultCountry(string $countryCode): static
    {
        $this->custom = array_merge($this->custom, [
            'data-default-country' => $countryCode,
        ]);
        return $this;
    }
}