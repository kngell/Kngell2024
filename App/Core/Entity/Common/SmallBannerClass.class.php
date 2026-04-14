<?php

declare(strict_types=1);

enum SmallBannerClass: string
{
    private const WIDTH = [
        'banner-left__wide' => ['mobile' => 400, 'tablet' => 600, 'desktop' => 800],
        'banner-square@light' => ['mobile' => 200, 'tablet' => 300, 'desktop' => 400],
        'banner-square@dark' => ['mobile' => 200, 'tablet' => 300, 'desktop' => 400],
        'banner-right' => ['mobile' => 300, 'tablet' => 450, 'desktop' => 600],
    ];

    public function getWidth(string $screen, array $overrides = []): int
    {
        // Check for override first
        if (isset($overrides[$this->value][$screen])) {
            return $overrides[$this->value][$screen];
        }

        // Fall back to default
        $widths = self::WIDTH[$this->value] ?? [];
        return $widths[$screen] ?? 0;
    }

    public function getWidths(array $overrides = []): array
    {
        // Get defaults
        $defaults = self::WIDTH[$this->value] ?? [];

        // Get overrides for this specific enum case
        $caseOverrides = $overrides[$this->value] ?? [];

        // Merge (overrides take precedence)
        return array_merge($defaults, $caseOverrides);
    }

    public function getClasses(): array
    {
        return explode('@', $this->value);
    }

    public function getBaseClass(): string
    {
        return explode('@', $this->value)[0];
    }

    public function getModifier(): ?string
    {
        $parts = explode('@', $this->value);
        return $parts[1] ?? null;
    }

    /**
     * Organize responses dynamically, supporting multiple banners per position.
     *
     * @param SmallBannerResponse[] $responses
     *
     * @return array<string, SmallBannerResponse|SmallBannerResponse[]>
     */
    public function getOrganizedWithCollections(array $responses): array
    {
        $grouped = [];

        foreach ($responses as $response) {
            $box = $response->getClass();
            $enumCase = self::tryFrom($box);

            if ($enumCase !== null) {
                $key = $enumCase->value;

                // Check if this position typically has multiple items
                if (str_contains($key, 'square')) {
                    $grouped[$key][] = $response;
                } else {
                    $grouped[$key] = $response;
                }
            }
        }

        return $grouped;
    }

    private function getLabel(): string
    {
        return ucwords(strtolower(str_replace('_', ' ', $this->name)));
    }

    public static function getOptions(): array
    {
        $options = [
            '' => '',
        ];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    public static function getOrganized(array $responses): array
    {
        return array_reduce($responses, function (array $carry, SmallBannerResponse $response) {
            $box = $response->getClass();
            if ($box !== null) {
                $enumCase = SmallBannerClass::tryFrom($box);

                if ($enumCase !== null) {
                    $carry[$enumCase->value] = $response;
                }

                return $carry;
            }
            return [];
        }, []);
    }

    case LEFT_WIDE = 'banner-left__wide';
    case SQUARE_LIGHT = 'banner-square@light';
    case SQUARE_DARK = 'banner-square@dark';
    case RIGHT = 'banner-right';
}