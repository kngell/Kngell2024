<?php

declare(strict_types=1);

class ScooterElectricite extends AbstractScooter
{
    public function afficheCaracteristiques(): void
    {
        echo "Scooter électrique - marque: $this->marque"
            . ", couleur: $this->couleur"
            . ", puissance: $this->puissance" . PHP_EOL;
    }
}