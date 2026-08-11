<?php

declare(strict_types=1);

enum ConditionListMode: string
{
    case MODE_ADMIN = '__mode_admin';
    case MODE_RESTORABLE = '__mode_restorable';
    case MODE_FRONTEND = '__mode_frontend';
}