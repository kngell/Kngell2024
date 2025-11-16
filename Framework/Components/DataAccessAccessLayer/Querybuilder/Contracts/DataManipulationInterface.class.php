<?php

declare(strict_types=1);

interface DataManipulationInterface
{
    public function insert();

    public function update();

    public function delete();
}