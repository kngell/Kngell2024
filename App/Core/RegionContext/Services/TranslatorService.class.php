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
        private FileContentInterface $fileContentManager,
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

        // Si la traduction n'est pas trouvée dans la langue demandée, on tente le fallback
        if ($translation === $key && $locale !== $this->fallbackLocale) {
            $translation = $this->getTranslation($key, $this->fallbackLocale);
        }

        // Remplacement dynamique des paramètres ({param})
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
     * Charge tous les termes d'un domaine / d'une table de traduction spécifique (ex: "common", "hero").
     */
    public function loadDomain(string $domain, ?string $locale = null): void
    {
        $locale = $locale ?? $this->locale;
        $cacheKey = $this->getCacheKey($domain, $locale);

        // Tentative de récupération depuis le cache
        if ($this->cacheEnabled && $this->cache && $this->cache->exists($cacheKey)) {
            $translations = $this->cache->get($cacheKey);
            $this->mergeTranslations($translations, $locale);
            return;
        }

        // Résolution du fichier et chargement sécurisé via le FileContentManager
        $filePath = $this->getDomainFilePath($domain, $locale);
        $translations = $this->loadTranslationsFromSource($filePath, $domain);

        // Sauvegarde dans le cache si activé
        if ($this->cacheEnabled && $this->cache) {
            $this->cache->set($cacheKey, $translations, 3600); // Cache d'une heure
        }

        $this->mergeTranslations($translations, $locale);
    }

    /**
     * Vide le registre des traductions chargées en mémoire (pratique pour les tests unitaires).
     */
    public function clear(): void
    {
        $this->translations = [];
    }

    /**
     * Scane le dossier racine des langues pour lister les locales disponibles.
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
        // Si le terme complet est déjà en mémoire, on le renvoie directement
        if (isset($this->translations[$locale][$key])) {
            return $this->translations[$locale][$key];
        }

        // Extraction du domaine (ex: "common.yes" -> domaine = "common", sous-clé = "yes")
        $parts = explode('.', $key, 2);
        if (count($parts) !== 2) {
            return $key; // Format de clé invalide
        }

        [$domain, $itemKey] = $parts;

        // Lazy-loading du fichier de domaine s'il n'est pas encore instancié
        $this->loadDomain($domain, $locale);

        return $this->translations[$locale][$key] ?? $key;
    }

    private function loadTranslationsFromSource(string $filePath, string $domain): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // Traitement sécurisé du format PHP (sans eval()) via ton gestionnaire
        if ($extension === 'php') {
            $translations = $this->fileContentManager->executePhpFile($filePath);
            return $this->flattenArray(is_array($translations) ? $translations : [], $domain);
        }

        // Traitement du format JSON via ton objet de contenu typé JsonFile
        if ($extension === 'json') {
            $jsonFile = new JsonFile($filePath, $this->fileContentManager);
            return $this->flattenArray($jsonFile->getContentAsArray(), $domain);
        }

        // Lecture brute du contenu pour les autres extensions secondaires (YML, INI)
        $content = $this->fileContentManager->read($filePath);

        return match($extension) {
            'yml', 'yaml' => $this->loadYamlTranslations($content, $domain),
            default => $this->loadIniTranslations($content, $domain),
        };
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
     * Aplatit de manière récursive les dictionnaires multidimensionnels en notation pointée (dot notation).
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

    /**
     * Enregistre les traductions plates directement sous l'arborescence de la locale correspondante.
     */
    private function mergeTranslations(array $newTranslations, string $locale): void
    {
        foreach ($newTranslations as $key => $value) {
            $this->translations[$locale][$key] = $value;
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

        // Route fallback par défaut vers un fichier PHP si aucun n'est trouvé
        return sprintf('%s/%s/%s.php', $this->translationsPath, $locale, $domain);
    }

    private function getCacheKey(string $domain, string $locale): string
    {
        return 'translations.' . $domain . '.' . $locale;
    }
}