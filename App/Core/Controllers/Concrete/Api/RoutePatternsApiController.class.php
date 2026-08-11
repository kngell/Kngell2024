<?php

declare(strict_types=1);

class RoutePatternsApiController extends Controller implements JavascriptApiInterface
{
    private const string RULES_PATH = APP . 'Config' . DS;

    public function __construct(
        private RoutePatternExporter $exporter,
    ) {
    }

    public function getSettings(): JsonResponse
    {
        $group = $this->request->getQuery()->get('group', 'all');

        try {
            $data = $this->exporter->exportForClient($group);
            return new JsonResponse($data);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to get route patterns: ' . $e->getMessage()],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function clearCache(): JsonResponse
    {
        if (Environment::isProduction()) {
            return new JsonResponse(
                ['error' => 'Cache clearing not allowed in production'],
                HttpStatusCode::HTTP_FORBIDDEN,
            );
        }

        $cleared = $this->exporter->clearCache('routes.yaml');

        return new JsonResponse([
            'success' => $cleared,
            'message' => 'Route patterns cache cleared',
        ]);
    }
}