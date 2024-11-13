<?php

declare(strict_types=1);

interface EntityManagerInterface
{
    /**
     * --------------------------------------------------------------------------------------------------
     * Insert query.
     * @return CrudInterface
     */
    public function getCrud(): CrudInterface;
}
