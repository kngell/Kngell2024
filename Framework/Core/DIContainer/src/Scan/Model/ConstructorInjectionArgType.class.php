<?php

declare(strict_types=1);

enum ConstructorInjectionArgType:string
{
    case BEAN = 'BEAN';
    case PROPERTY = 'PROPERTY';
}