<?php

declare(strict_types=1);

interface ClassesMappingInterface
{
    /**
     * @return ReflectionClass[]
     */
    public function getClassesToScan() : array;
}