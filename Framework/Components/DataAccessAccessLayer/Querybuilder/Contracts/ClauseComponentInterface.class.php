<?php

declare(strict_types=1);

interface ClauseComponentInterface
{
    public function getSqlClause(): null|SqlClause|SqlCteClause|SqlKeyWord;
}