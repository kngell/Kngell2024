<?php

declare(strict_types=1);
class Users extends Entity
{
    #[EntityFieldId(name: 'userID')]
    private int $id;
    private string $lastName;
    private string $FirstName;

    private string $email;
}
