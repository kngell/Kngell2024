<?php

declare(strict_types=1);
// Instead of this (expensive):
$reflection = new ReflectionClass(User::class);
$property = $reflection->getProperty('email');
$property->setAccessible(true);
$property->getValue($user);

// Do this (cached):
$reflection = CustomReflection::getInstance(User::class);
$email = $reflection->getPropertyValue($user, 'email');