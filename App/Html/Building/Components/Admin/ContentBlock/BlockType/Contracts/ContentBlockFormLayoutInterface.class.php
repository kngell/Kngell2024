<?php

declare(strict_types=1);

interface ContentBlockFormLayoutInterface
{
    public function getSectionGroups(): ?SectionGroupManager;

    public function getTabConfig(): ?TabConfig;
}