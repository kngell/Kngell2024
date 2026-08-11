<?php

declare(strict_types=1);

final class SectionGroupManager
{
    /** @var SectionGroup[] */
    private array $groups = [];

    private array $sectionToGroupMap = [];

    public function addGroup(SectionGroup $group): self
    {
        $this->groups[$group->getKey()] = $group;

        foreach ($group->getSectionKeys() as $sectionKey) {
            $this->sectionToGroupMap[$sectionKey] = $group->getKey();
        }

        return $this;
    }

    public function removeGroup(string $groupKey): self
    {
        unset($this->groups[$groupKey]);
        return $this;
    }

    public function getGroup(string $groupKey): ?SectionGroup
    {
        return $this->groups[$groupKey] ?? null;
    }

    public function getAllGroups(): array
    {
        return $this->groups;
    }

    public function getGroupForSection(string $sectionKey): ?string
    {
        return $this->sectionToGroupMap[$sectionKey] ?? null;
    }

    public function getAllSectionKeys(): array
    {
        return array_keys($this->sectionToGroupMap);
    }

    public function hasGroups(): bool
    {
        return !empty($this->groups);
    }

    public static function create(): self
    {
        return new self();
    }
}