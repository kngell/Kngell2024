<?php

declare(strict_types=1);

class ScooterEssence extends AbstractScooter
{
    public function afficheCaracteristiques(): void
    {
        echo "Scooter à essence - marque : $this->marque"
            . ", couleur: $this->couleur"
            . ", puissance: $this->puissance" . PHP_EOL;
    }
}