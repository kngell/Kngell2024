<?php

declare(strict_types=1);

class FlexibleArraySplitter
{
    private array $array;
    private int $totalItems;
    private array $groups = [];
    private array $groupNames = [];

    public function __construct(array $array)
    {
        $this->array = $array;
        $this->totalItems = count($array);
    }

    /**
     * Split array with custom group sizes.
     *
     * @param array $specifiedGroups Associative array of group names and their sizes
     * @param int|null $numberOfAutoGroups Number of auto-distributed groups (null = one group for remaining)
     *
     * @return self Returns $this for method chaining
     */
    public function split(array $specifiedGroups = [], ?int $numberOfAutoGroups = null): self
    {
        $start = 0;
        $this->groups = [];
        $this->groupNames = [];

        // Process specified groups
        foreach ($specifiedGroups as $groupName => $size) {
            $size = (int) $size;
            $this->groupNames[] = $groupName;

            if ($start >= $this->totalItems) {
                $this->groups[$groupName] = [];
                continue;
            }

            $actualSize = min($size, $this->totalItems - $start);
            $this->groups[$groupName] = array_slice($this->array, $start, $actualSize);
            $start += $actualSize;
        }

        // Process remaining items
        $remaining = $this->totalItems - $start;

        if ($remaining > 0) {
            if ($numberOfAutoGroups !== null && $numberOfAutoGroups > 0) {
                $itemsPerGroup = (int) ceil($remaining / $numberOfAutoGroups);
                $itemsPerGroup = max(1, $itemsPerGroup);

                for ($i = 1; $i <= $numberOfAutoGroups; $i++) {
                    $groupName = "auto_group_{$i}";
                    $this->groupNames[] = $groupName;

                    if ($start >= $this->totalItems) {
                        $this->groups[$groupName] = [];
                    } else {
                        $this->groups[$groupName] = array_slice($this->array, $start, $itemsPerGroup);
                        $start += $itemsPerGroup;
                    }
                }
            } else {
                // Put all remaining in one group
                $this->groups['remaining'] = array_slice($this->array, $start);
                $this->groupNames[] = 'remaining';
            }
        }

        return $this;
    }

    /**
     * Get a specific group by name.
     *
     * @param string $groupName The group name (e.g., 'first_group', 'remaining', 'auto_group_1')
     *
     * @return array
     */
    public function get(string $groupName): array
    {
        return $this->groups[$groupName] ?? [];
    }

    /**
     * Get first group.
     *
     * @return array
     */
    public function getFirst(): array
    {
        return $this->getFirstGroup();
    }

    /**
     * Get first group (alias).
     *
     * @return array
     */
    public function getFirstGroup(): array
    {
        if (empty($this->groupNames)) {
            return [];
        }

        $firstGroupName = $this->groupNames[0];
        return $this->groups[$firstGroupName] ?? [];
    }

    /**
     * Get last group.
     *
     * @return array
     */
    public function getLastGroup(): array
    {
        if (empty($this->groupNames)) {
            return [];
        }

        $lastGroupName = $this->groupNames[count($this->groupNames) - 1];
        return $this->groups[$lastGroupName] ?? [];
    }

    /**
     * Get remaining group (if exists).
     *
     * @return array
     */
    public function getRemaining(): array
    {
        return $this->get('remaining');
    }

    /**
     * Get a specific auto group by index.
     *
     * @param int $index Auto group number (1-based)
     *
     * @return array
     */
    public function getAutoGroup(int $index): array
    {
        return $this->get("auto_group_{$index}");
    }

    /**
     * Get all auto groups.
     *
     * @return array
     */
    public function getAutoGroups(): array
    {
        $autoGroups = [];
        foreach ($this->groups as $name => $group) {
            if (strpos($name, 'auto_group_') === 0) {
                $autoGroups[$name] = $group;
            }
        }
        return $autoGroups;
    }

    /**
     * Get all groups.
     *
     * @return array
     */
    public function getAllGroups(): array
    {
        return $this->groups;
    }

    /**
     * Get group names.
     *
     * @return array
     */
    public function getGroupNames(): array
    {
        return $this->groupNames;
    }

    /**
     * Get a group by index.
     *
     * @param int $index Zero-based index
     *
     * @return array
     */
    public function getGroupByIndex(int $index): array
    {
        if (!isset($this->groupNames[$index])) {
            return [];
        }

        return $this->get($this->groupNames[$index]);
    }

    /**
     * Get group count.
     *
     * @return int
     */
    public function getGroupCount(): int
    {
        return count($this->groups);
    }

    /**
     * Check if a group exists.
     *
     * @param string $groupName
     *
     * @return bool
     */
    public function hasGroup(string $groupName): bool
    {
        return isset($this->groups[$groupName]);
    }

    /**
     * Simplified method for common use case.
     *
     * @param int|null $firstSize Size of first group
     * @param int|null $secondSize Size of second group (optional)
     * @param int|null $autoGroups Number of auto groups (optional)
     *
     * @return self
     */
    public function splitSimple(?int $firstSize = null, ?int $secondSize = null, ?int $autoGroups = null): self
    {
        $specified = [];

        if ($firstSize !== null) {
            $specified['first_group'] = $firstSize;
        }

        if ($secondSize !== null) {
            $specified['second_group'] = $secondSize;
        }

        return $this->split($specified, $autoGroups);
    }

    /**
     * @param array $array
     *
     * @return FlexibleArraySplitter
     */
    public function setArray(array $array): FlexibleArraySplitter
    {
        $this->array = $array;
        $this->totalItems = count($array);
        return $this;
    }
}