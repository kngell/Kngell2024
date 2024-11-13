<?php

declare(strict_types=1);

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

#[Configuration]
class SerializerConfig
{
    #[Bean]
    public function JmsSerializer() : Serializer
    {
        return SerializerBuilder::create()->build();
    }
}