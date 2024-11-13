<?php

declare(strict_types=1);

use JMS\Serializer\Serializer;

readonly class RouteResponseGenerator
{
    private Serializer $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function generate(ResponseBody $responseBody, ResponseStatus|null $responseStatus, mixed $returnValue) : Response
    {
        $status = $responseStatus->statusCode !== null ? $responseStatus->statusCode : HttpStatusCode::HTTP_OK;
        $content = '';
        $contentTypeHeaderValue = '';

        switch ($responseBody->type) {
            case ResponseBodyType::JSON:
                $content = $this->serializer->serialize($returnValue, 'json');
                $contentTypeHeaderValue = $responseBody->produces ?? 'application/json';
                break;
            case ResponseBodyType::XML:
                $content = $this->serializer->serialize($returnValue, 'xml');
                $contentTypeHeaderValue = $responseBody->produces ?? 'text/xml';
                break;
            case ResponseBodyType::RAW:
                $content = (string) $returnValue;
                $contentTypeHeaderValue = $responseBody->produces ?? 'text/plain';
                break;

            default:
                // code...
                break;
        }
        return new Response($content, $status, ['Content-Type' => $contentTypeHeaderValue]);
    }
}