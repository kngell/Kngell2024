<?php

declare(strict_types=1);
class SessionRegionContext implements RegionContextResolutionInterface
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function resolveRegion(): ?string
    {
        if ($this->session->exists('user_region')) {
            return $this->session->get('user_region');
        }
        return null;
    }

    public function getPriority(): int
    {
        return 70;
    }
}