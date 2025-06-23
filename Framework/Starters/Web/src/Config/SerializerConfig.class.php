<?php

declare(strict_types=1);

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class SerializerConfig
{
    public function JmsSerializer() : Serializer
    {
        return SerializerBuilder::create()->build();
    }
}