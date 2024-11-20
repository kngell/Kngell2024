<?php

declare(strict_types=1);

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

readonly class RouteResponseGenerator
{
    private Serializer $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct()
    {
        $this->serializer = SerializerBuilder::create()->build();
    }

    public function generate(ResponseStatus|null $responseStatus, mixed $returnValue) : Response
    {
        $status = $responseStatus->statusCode !== null ? $responseStatus->statusCode : HttpStatusCode::HTTP_OK;

        return new Response($returnValue, $status);
    }
}