<?php

declare(strict_types=1);

final class DateTimePresenter implements TypePresenterInterface
{
    public function __construct(
        private RegionContextInterface $region,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!$value instanceof DateTimeInterface) {
            return (string) $value;
        }

        $region = $regionContext ?? $this->region;

        // Get display preferences from property attributes
        $displayType = 'auto'; // 'auto', 'date', 'time', 'datetime', 'relative'
        $customFormat = null;
        $dateStyle = null;
        $timeStyle = null;

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $formatAttr = $attributes[0]->newInstance();
                $displayType = $formatAttr->style ?? $displayType;
                $customFormat = $formatAttr->format ?? $customFormat;
                $dateStyle = $formatAttr->dateStyle ?? $dateStyle;
                $timeStyle = $formatAttr->timeStyle ?? $timeStyle;
            }
        }

        // 1. If custom format is provided, use it
        if ($customFormat !== null) {
            return $value->format($customFormat);
        }

        // 2. Handle based on display type
        return match($displayType) {
            'date' => $this->formatDate($value, $region, $dateStyle),
            'time' => $this->formatTime($value, $region, $timeStyle),
            'datetime' => $this->formatDateTime($value, $region, $dateStyle, $timeStyle),
            'relative' => $this->formatRelative($value, $region),
            'auto' => $this->formatAuto($value, $region, $dateStyle, $timeStyle),
            default => $this->formatAuto($value, $region, $dateStyle, $timeStyle),
        };
    }

    /**
     * Format as date only.
     */
    private function formatDate(DateTimeInterface $dateTime, RegionContextInterface $region, ?string $style = null): string
    {
        // If style is provided (could be format string like 'Y-m-d'), use it
        if ($style !== null && $this->isFormatString($style)) {
            return $dateTime->format($style);
        }

        // Otherwise, use region's date format
        return $region->formatDate($dateTime, $style);
    }

    /**
     * Format as time only - FIXED: No formatTime method, use getTimeFormat().
     */
    private function formatTime(DateTimeInterface $dateTime, RegionContextInterface $region, ?string $style = null): string
    {
        // If style is provided (format string), use it
        if ($style !== null && $this->isFormatString($style)) {
            return $dateTime->format($style);
        }

        // Use region's time format string
        $timeFormat = $style ?? $region->getTimeFormat();
        return $dateTime->format($timeFormat);
    }

    /**
     * Format as date and time.
     */
    private function formatDateTime(DateTimeInterface $dateTime, RegionContextInterface $region, ?string $dateStyle = null, ?string $timeStyle = null): string
    {
        // If custom styles are format strings, combine them
        if (($dateStyle !== null && $this->isFormatString($dateStyle)) ||
            ($timeStyle !== null && $this->isFormatString($timeStyle))) {
            $dateFormat = $dateStyle ?? $region->getDateFormat();
            $timeFormat = $timeStyle ?? $region->getTimeFormat();

            return $dateTime->format($dateFormat . ' ' . $timeFormat);
        }

        // Use region's datetime format method with styles if provided
        if ($dateStyle !== null || $timeStyle !== null) {
            $dateFormat = $dateStyle ?? $region->getDateFormat();
            $timeFormat = $timeStyle ?? $region->getTimeFormat();
            return $dateTime->format($dateFormat . ' ' . $timeFormat);
        }

        // Default: use region's datetime format
        return $region->formatDateTime($dateTime);
    }

    /**
     * Auto-detect the best format.
     */
    private function formatAuto(DateTimeInterface $dateTime, RegionContextInterface $region, ?string $dateStyle = null, ?string $timeStyle = null): string
    {
        // If it's a date-only value (time at midnight), show date only
        if ($this->isDateOnly($dateTime)) {
            return $this->formatDate($dateTime, $region, $dateStyle);
        }

        // If it's today, show time only
        if ($this->isToday($dateTime)) {
            return $this->formatTime($dateTime, $region, $timeStyle);
        }

        // If within last 7 days, show relative date
        if ($this->isWithinLastDays($dateTime, 7)) {
            return $this->formatRelative($dateTime, $region);
        }

        // Otherwise show date only (for older dates)
        return $this->formatDate($dateTime, $region, $dateStyle);
    }

    /**
     * Format relative time.
     */
    private function formatRelative(DateTimeInterface $dateTime, RegionContextInterface $region): string
    {
        $now = new DateTimeImmutable('now', $dateTime->getTimezone());
        $diff = $now->diff($dateTime);

        // Future dates
        if ($dateTime > $now) {
            if ($diff->days === 0) {
                if ($diff->h < 1) {
                    if ($diff->i < 1) {
                        return 'in a few seconds';
                    }
                    return $diff->i === 1 ? 'in 1 minute' : 'in ' . $diff->i . ' minutes';
                }
                return $diff->h === 1 ? 'in 1 hour' : 'in ' . $diff->h . ' hours';
            }
            if ($diff->days === 1) {
                return 'tomorrow';
            }
            if ($diff->days < 7) {
                return 'in ' . $diff->days . ' days';
            }
            if ($diff->days < 30) {
                return 'in ' . floor($diff->days / 7) . ' weeks';
            }
            return $this->formatDate($dateTime, $region);
        }

        // Past dates
        if ($diff->days === 0) {
            if ($diff->h < 1) {
                if ($diff->i < 1) {
                    return 'just now';
                }
                return $diff->i === 1 ? '1 minute ago' : $diff->i . ' minutes ago';
            }
            return $diff->h === 1 ? '1 hour ago' : $diff->h . ' hours ago';
        }
        if ($diff->days === 1) {
            return 'yesterday';
        }
        if ($diff->days < 7) {
            return $diff->days . ' days ago';
        }
        if ($diff->days < 30) {
            return floor($diff->days / 7) . ' weeks ago';
        }

        return $this->formatDate($dateTime, $region);
    }

    /**
     * Check if a string is a PHP date format string.
     */
    private function isFormatString(?string $str): bool
    {
        if ($str === null) {
            return false;
        }

        // Common PHP date format characters
        $formatChars = ['Y', 'y', 'm', 'n', 'd', 'j', 'H', 'h', 'G', 'g', 'i', 's', 'a', 'A'];

        foreach ($formatChars as $char) {
            if (str_contains($str, $char)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if datetime is date-only (time at midnight).
     */
    private function isDateOnly(DateTimeInterface $dateTime): bool
    {
        return $dateTime->format('H:i:s') === '00:00:00';
    }

    /**
     * Check if datetime is today.
     */
    private function isToday(DateTimeInterface $dateTime): bool
    {
        $now = new DateTimeImmutable();
        return $dateTime->format('Y-m-d') === $now->format('Y-m-d');
    }

    /**
     * Check if datetime is within last N days.
     */
    private function isWithinLastDays(DateTimeInterface $dateTime, int $days): bool
    {
        $now = new DateTimeImmutable();
        $interval = new DateInterval('P' . $days . 'D');
        $pastDate = $now->sub($interval);

        return $dateTime >= $pastDate;
    }
}