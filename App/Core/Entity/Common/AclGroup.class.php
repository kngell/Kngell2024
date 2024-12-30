<?php

declare(strict_types=1);

class AclGroup extends Entity
{
    private int $grId;
    private string $name;
    private string $permission;
}