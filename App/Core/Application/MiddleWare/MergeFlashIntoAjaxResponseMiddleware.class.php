<?php

declare(strict_types=1);

class MergeFlashIntoAjaxResponseMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly FlashRenderer $flashRenderer,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $response = $next->handle($request);

        if (!$request->isAjax() || !$response instanceof JsonResponse) {
            return $response;
        }

        $payload = $this->normalizePayload($response->getData());
        $isRedirect = $response->getHeaders()->has('X-Redirect-Action');

        $sessionFlashes = $this->flashRenderer->toArray(consume: !$isRedirect);

        if (empty($sessionFlashes)) {
            return $response;
        }

        $payload['flashes'] = array_merge(
            $payload['flashes'] ?? [],
            $sessionFlashes,
        );

        $response->setData($payload);

        return $response;
    }

    private function normalizePayload(array|object|string|bool $data): array
    {
        // Already an associative array
        if (is_array($data)) {
            return $data;
        }

        // Object → cast to array
        if (is_object($data)) {
            return (array) $data;
        }

        // JSON string passed through directly
        if (is_string($data) && $data !== '') {
            try {
                $decoded = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
                return is_array($decoded) ? $decoded : ['data' => $decoded];
            } catch (JsonException) {
                return ['data' => $data];
            }
        }

        return ['data' => $data];
    }
}