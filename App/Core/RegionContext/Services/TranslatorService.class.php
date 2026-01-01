<?php

declare(strict_types=1);

class TranslatorService implements TranslatorServiceInterface
{
    private string $locale;
    private string $fallbackLocale;
    private array $translations = [];
    private string $translationsPath;
    private bool $cacheEnabled;
    private ?CacheInterface $cache = null;

    public function __construct(
        string $defaultLocale = 'en_US',
        string $fallbackLocale = 'en_US',
        string $translationsPath = '',
        bool $cacheEnabled = true,
        ?CacheInterface $cache = null,
    ) {
        $this->locale = $defaultLocale;
        $this->fallbackLocale = $fallbackLocale;
        $this->translationsPath = $translationsPath ?: $this->getDefaultTranslationsPath();
        $this->cacheEnabled = $cacheEnabled;
        $this->cache = $cache;
    }

    public function translate(string $key, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;
        $translation = $this->getTranslation($key, $locale);

        // If not found in requested locale, try fallback
        if ($translation === $key && $locale !== $this->fallbackLocale) {
            $translation = $this->getTranslation($key, $this->fallbackLocale);
        }

        // Replace parameters
        if (!empty($parameters)) {
            $translation = $this->replaceParameters($translation, $parameters);
        }

        return $translation;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function has(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->locale;
        $translation = $this->getTranslation($key, $locale);

        return $translation !== $key ||
               ($locale !== $this->fallbackLocale && $this->getTranslation($key, $this->fallbackLocale) !== $key);
    }

    /**
     * Load all translations for a specific domain/locale.
     */
    public function loadDomain(string $domain, ?string $locale = null): void
    {
        $locale = $locale ?? $this->locale;
        $cacheKey = $this->getCacheKey($domain, $locale);

        // Try cache first
        if ($this->cacheEnabled && $this->cache && $this->cache->exists($cacheKey)) {
            $translations = $this->cache->get($cacheKey);
            $this->mergeTranslations($translations, $domain);
            return;
        }

        // Load from file
        $filePath = $this->getDomainFilePath($domain, $locale);
        $translations = $this->loadTranslationsFromFile($filePath, $domain);

        // Cache if enabled
        if ($this->cacheEnabled && $this->cache) {
            $this->cache->set($cacheKey, $translations, 3600); // 1 hour
        }

        $this->mergeTranslations($translations, $domain);
    }

    /**
     * Clear loaded translations (useful for testing).
     */
    public function clear(): void
    {
        $this->translations = [];
    }

    /**
     * Get available locales.
     */
    public function getAvailableLocales(): array
    {
        $locales = [];
        $path = $this->translationsPath;

        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_dir($path . '/' . $file)) {
                    $locales[] = $file;
                }
            }
        }

        return $locales;
    }

    private function getTranslation(string $key, string $locale): string
    {
        // Check if already loaded
        if (isset($this->translations[$locale][$key])) {
            return $this->translations[$locale][$key];
        }

        // Parse key to extract domain (e.g., "common.yes" -> domain="common", key="yes")
        $parts = explode('.', $key, 2);
        if (count($parts) !== 2) {
            return $key; // Invalid key format
        }

        [$domain, $itemKey] = $parts;

        // Lazy-load the domain
        $this->loadDomain($domain, $locale);

        // Return translation or original key if not found
        return $this->translations[$locale][$key] ?? $key;
    }

    private function loadTranslationsFromFile(string $filePath, string $domain): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);

        // Support different formats
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match($extension) {
            'php' => $this->loadPhpTranslations($content, $domain),
            'json' => $this->loadJsonTranslations($content, $domain),
            'yml', 'yaml' => $this->loadYamlTranslations($content, $domain),
            default => $this->loadIniTranslations($content, $domain),
        };
    }

    private function loadPhpTranslations(string $content, string $domain): array
    {
        $translations = eval('?>' . $content);
return $this->flattenArray($translations ?? [], $domain);
}

private function loadJsonTranslations(string $content, string $domain): array
{
$translations = json_decode($content, true);
return $this->flattenArray($translations ?? [], $domain);
}

private function loadYamlTranslations(string $content, string $domain): array
{
if (!function_exists('yaml_parse')) {
throw new RuntimeException('YAML extension not installed');
}
$translations = yaml_parse($content);
return $this->flattenArray($translations ?? [], $domain);
}

private function loadIniTranslations(string $content, string $domain): array
{
$translations = parse_ini_string($content, true, INI_SCANNER_TYPED);
return $this->flattenArray($translations ?? [], $domain);
}

/**
* Flatten nested array to dot notation.
*/
private function flattenArray(array $array, string $prefix = ''): array
{
$result = [];
foreach ($array as $key => $value) {
$newKey = $prefix ? $prefix . '.' . $key : $key;

if (is_array($value)) {
$result = array_merge($result, $this->flattenArray($value, $newKey));
} else {
$result[$newKey] = $value;
}
}
return $result;
}

private function mergeTranslations(array $newTranslations, string $domain): void
{
foreach ($newTranslations as $key => $value) {
// Extract locale from key if present in format "locale.domain.key"
$keyParts = explode('.', $key, 3);

if (count($keyParts) === 3) {
[$locale, $actualDomain, $itemKey] = $keyParts;
$fullKey = $actualDomain . '.' . $itemKey;
$this->translations[$locale][$fullKey] = $value;
} else {
// Use current locale
$this->translations[$this->locale][$key] = $value;
}
}
}

private function replaceParameters(string $translation, array $parameters): string
{
foreach ($parameters as $key => $value) {
$placeholder = '{' . $key . '}';
$translation = str_replace($placeholder, (string) $value, $translation);
}
return $translation;
}

private function getDefaultTranslationsPath(): string
{
return dirname(__DIR__, 3) . '/translations';
}

private function getDomainFilePath(string $domain, string $locale): string
{
// Try multiple file formats
$formats = ['php', 'json', 'yml', 'ini'];

foreach ($formats as $format) {
$filePath = sprintf(
'%s/%s/%s.%s',
$this->translationsPath,
$locale,
$domain,
$format,
);

if (file_exists($filePath)) {
return $filePath;
}
}

// Return a non-existent path for domains without translations
return sprintf(
'%s/%s/%s.php',
$this->translationsPath,
$locale,
$domain,
);
}

private function getCacheKey(string $domain, string $locale): string
{
return 'translations.' . $domain . '.' . $locale;
}
}