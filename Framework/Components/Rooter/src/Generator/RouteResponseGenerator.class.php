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

    public function generate(?ResponseBody $responseBody, ?ResponseStatus $responseStatus, mixed $returnValue, Response $response) : Response
    {
        $responseStatus = $this->ResponseStatus($responseStatus);
        $status = $responseStatus->statusCode !== null ? $responseStatus->statusCode : HttpStatusCode::HTTP_OK;
        if ($responseBody === null) {
            return $response->setContent($returnValue)->setStatusCode($status);
        }
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
                $content = $returnValue;
                $contentTypeHeaderValue = $responseBody->produces ?? 'text/html';
                break;
        }
        return $response
            ->setContent($content)
            ->setStatusCode($status)
            ->setHeader('Content-Type', $contentTypeHeaderValue);
    }

    private function ResponseStatus(?ResponseStatus $responseStatus) : ResponseStatus
    {
        if ($responseStatus === null) {
            return new ResponseStatus(HttpStatusCode::HTTP_OK);
        }
        return $responseStatus;
    }
}