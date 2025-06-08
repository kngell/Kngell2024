<?php

declare(strict_types=1);

class UserRepository extends Repository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }
}