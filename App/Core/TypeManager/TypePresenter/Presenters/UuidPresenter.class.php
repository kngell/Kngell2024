<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

class UuidPresenter implements TypePresenterInterface
{
    public function __construct(
        private bool $shorten = false,
        private int $shortLength = 8,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof UuidInterface;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!$value instanceof UuidInterface) {
            return (string) $value;
        }

        // Check for display preferences
        $format = 'standard';
        $shorten = $this->shorten;

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $formatAttr = $attributes[0]->newInstance();
                $format = $formatAttr->style ?? $format;
                $shorten = $formatAttr->shorten ?? $shorten;
            }
        }

        // Convert to string based on format
        $uuidString = match($format) {
            'hex' => $value->getHex()->toString(),
            'bytes' => bin2hex($value->getBytes()),
            'urn' => $value->getUrn(),
            'integer' => (string) $value->getInteger(),
            'standard' => $value->toString(),
            default => $value->toString(),
        };

        // Shorten if requested
        if ($shorten) {
            return $this->shortenUuid($uuidString);
        }

        return $uuidString;
    }

    private function shortenUuid(string $uuid): string
    {
        // Remove hyphens and take first N characters
        $clean = str_replace('-', '', $uuid);
        return substr($clean, 0, $this->shortLength);
    }
}