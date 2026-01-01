<?php

declare(strict_types=1);
class AcceptLanguageRegionContext extends AbstractRegionContext
{
    public function __construct(private Request $request)
    {
    }

    public function resolveRegion(): ?string
    {
        if (!$this->request->getHeaders()->has('Accept-Language')) {
            return null;
        }

        $acceptLanguage = $this->request->getHeaders()->get('Accept-Language');
        $locales = explode(',', (string) $acceptLanguage);
        $primaryLocale = trim($locales[0]);

        if (str_contains($primaryLocale, '-')) {
            $parts = explode('-', $primaryLocale);
            return isset($parts[1]) && strlen($parts[1]) === 2 ? strtoupper($parts[1]) : null;
        }

        return null;
    }

    public function getPriority(): int
    {
        return 80;
    }

    public function getName(): string
    {
        return 'browser_language';
    }
}