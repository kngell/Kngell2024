<?php

declare(strict_types=1);
class RegionProviderService
{
    private ?Region $region;

    public function __construct(private RegionModel $regionModel)
    {
    }

    public function isValidRegion(string $regionCode): bool
    {
        $this->region = $this->regionModel->one([
            'region_code' => $regionCode,
            'is_active' => true,
        ])->asClass();

        return $this->region !== null;
    }

    /**
     * @return null|Region
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }
}