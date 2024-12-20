<?php

declare(strict_types=1);

class UsersModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }
}
