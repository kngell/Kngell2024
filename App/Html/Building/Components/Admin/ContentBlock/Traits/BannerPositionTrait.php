<?php

declare(strict_types=1);
/**
 * @method static static[] cases()
 * @method static static|null tryFrom(string $value)
 *
 * @property string $value
 * @property string $name
 */
trait BannerPositionTrait
{
    /**
     * Get width for specific screen size.
     */
    public function getWidth(string $screen, array $overrides = []): int
    {
        $config = static::getWidthConfig();

        if (isset($overrides[$this->value][$screen])) {
            return $overrides[$this->value][$screen];
        }

        $widths = $config[$this->value] ?? [];
        return $widths[$screen] ?? 0;
    }

    /**
     * Get all widths for this position.
     */
    public function getWidths(array $overrides = []): array
    {
        $config = static::getWidthConfig();
        $defaults = $config[$this->value] ?? [];
        $caseOverrides = $overrides[$this->value] ?? [];
        return array_merge($defaults, $caseOverrides);
    }

    /**
     * Get classes from value (split by @).
     */
    public function getClasses(): array
    {
        return explode('@', $this->value);
    }

    /**
     * Get base class (before @).
     */
    public function getBaseClass(): string
    {
        return explode('@', $this->value)[0];
    }

    /**
     * Get modifier (after @) or null if not exists.
     */
    public function getModifier(): ?string
    {
        $parts = explode('@', $this->value);
        return $parts[1] ?? null;
    }

    /**
     * Organize responses dynamically, supporting multiple banners per position.
     *
     * @param ContentBlockCollectionResponse[] $responses
     *
     * @return array<string, ContentBlockCollectionResponse|ContentBlockCollectionResponse[]>
     */
    public function getOrganizedWithCollections(array $responses): array
    {
        $grouped = [];

        foreach ($responses as $response) {
            $box = $response->getClass();
            $enumCase = null;

            // Resolve enum case without relying on tryFrom (which may not exist in older PHP stan setups)
            if ($box !== null) {
                if (method_exists(static::class, 'cases')) {
                    foreach (static::cases() as $case) {
                        if ($case->value === $box) {
                            $enumCase = $case;
                            break;
                        }
                    }
                } else {
                    // Fallback for environments where enums/cases() are not available.
                    foreach (static::getAllValues() as $val) {
                        if ($val === $box) {
                            $enumCase = $val;
                            break;
                        }
                    }
                }
            }

            if ($enumCase !== null) {
                $key = $enumCase->value;
                if (str_contains($key, 'square')) {
                    $grouped[$key][] = $response;
                } else {
                    $grouped[$key] = $response;
                }
            }
        }

        return $grouped;
    }

    abstract public static function getAllValues(): array;

    public function getTitle(): string
    {
        return $this->getLabel();
    }

    abstract protected static function getWidthConfig(): array;

    /**
     * Get human-readable label.
     */
    private function getLabel(): string
    {
        return ucwords(strtolower(str_replace('_', ' ', $this->name)));
    }

    /**
     * Get organized responses (override if logic differs).
     */
    public static function getOrganized(array $responses): array
    {
        return array_reduce($responses, function (array $carry, ContentBlockCollectionResponse $response) {
            $box = $response->getClass();
            if ($box !== null) {
                $enumCase = static::tryFrom($box);
                if ($enumCase !== null) {
                    $carry[$enumCase->value] = $response;
                }
            }
            return $carry;
        }, []);
    }

    public static function getOptions(): array
    {
        $options = ['' => ''];

        if (method_exists(static::class, 'cases')) {
            foreach (static::cases() as $case) {
                $options[$case->value] = $case->getLabel();
            }
        } else {
            // Fallback: use declared values and generate labels.
            foreach (static::getAllValues() as $val) {
                $options[$val] = ucwords(strtolower(str_replace('_', ' ', $val)));
            }
        }

        return $options;
    }
}