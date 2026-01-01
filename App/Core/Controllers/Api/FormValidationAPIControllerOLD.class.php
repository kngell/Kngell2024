<?php

declare(strict_types=1);

class FormValidationApiControllerOLD extends Controller
{
    private const string RULES_PATH = APP . 'Html' . DS . 'Forms' . DS . 'Validator' . DS . 'Rules';

    public function __construct(
        private ValidationRulesExporter $rulesExporter,
        private FileSearchManager $searchFile,
    ) {
    }

    public function getRules(): JsonResponse
    {
        $rulesFile = $this->request->getQuery()->get('rules') ?? 'productRules';

        // Security: validate the rules file name
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $rulesFile)) {
            return new JsonResponse(
                ['error' => 'Invalid rules file name'],
                HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }

        $rulesPath = $this->searchFile->findFile(self::RULES_PATH, "{$rulesFile}.yaml");

        if ($rulesPath === null) {
            return new JsonResponse(['error' => 'file does not exist'], HttpStatusCode::HTTP_NOT_FOUND);
        }

        $rulesPath = $rulesPath->getPathname();

        if (!file_exists($rulesPath)) {
            return new JsonResponse(
                ['error' => 'Validation rules not found'],
                HttpStatusCode::HTTP_NOT_FOUND,
            );
        }

        try {
            $data = $this->rulesExporter->exportForClient($rulesPath);

            // Add debug info only in development mode
            if (Environment::isDebug() || $this->request->getQuery()->get('debug') === '1') {
                $data['_cache'] = $this->rulesExporter->getCacheStats($rulesPath);
                $data['_environment'] = Environment::get('APP_ENV');
            }

            return new JsonResponse($data, HttpStatusCode::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to load validation rules: ' . $e->getMessage()],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function getGlobalSettings(Request $request): JsonResponse
    {
        try {
            $settings = $this->rulesExporter->getGlobalSettings();

            // Add debug info only in development mode
            if (Environment::isDebug() || $request->getQuery()->get('debug') === '1') {
                $settings['_cache'] = $this->rulesExporter->getCacheStats();
                $settings['_environment'] = Environment::get('APP_ENV');
            }

            return new JsonResponse($settings, HttpStatusCode::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to load global settings: ' . $e->getMessage()],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function listAvailableRules(Request $request): JsonResponse
    {
        $rulesFiles = glob(APP . 'Forms/*.yaml');
        $availableRules = [];

        foreach ($rulesFiles as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $availableRules[] = [
                'name' => $name,
                'path' => $file,
                'modified' => filemtime($file),
                'size' => filesize($file),
            ];
        }

        $response = [
            'available_rules' => $availableRules,
            'count' => count($availableRules),
        ];

        // Add environment info in debug mode
        if (Environment::isDebug()) {
            $response['_environment'] = Environment::get('APP_ENV');
        }

        return new JsonResponse($response, HttpStatusCode::HTTP_OK);
    }

    public function getCacheStats(Request $request): JsonResponse
    {
        $rulesFile = $request->getQuery()->get('rules');
        $rulesPath = $rulesFile ? APP . "Forms/{$rulesFile}.yaml" : null;

        try {
            $stats = $this->rulesExporter->getCacheStats($rulesPath);

            // Add environment info in debug mode
            if (Environment::isDebug()) {
                $stats['_environment'] = Environment::get('APP_ENV');
            }

            return new JsonResponse($stats, HttpStatusCode::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to get cache stats: ' . $e->getMessage()],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function clearCache(Request $request): JsonResponse
    {
        // Only allow from webpack dev server or in development environment
        if (!$request->isFromWebpackDevServer() && Environment::isProduction()) {
            return new JsonResponse(
                ['error' => 'Cache clearing not allowed in production'],
                HttpStatusCode::HTTP_FORBIDDEN,
            );
        }

        $rulesFile = $request->getQuery()->get('rules');
        $rulesPath = $rulesFile ? APP . "Forms/{$rulesFile}.yaml" : null;

        try {
            $result = $this->rulesExporter->clearCache($rulesPath);
            return new JsonResponse([
                'success' => $result,
                'message' => $rulesFile ? "Cache cleared for {$rulesFile}" : 'All validation caches cleared',
                'environment' => Environment::get('APP_ENV'),
            ], HttpStatusCode::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to clear cache: ' . $e->getMessage()],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function getEnvironmentInfo(Request $request): JsonResponse
    {
        // Useful for debugging
        $info = [
            'app_env' => Environment::get('APP_ENV'),
            'node_env' => Environment::get('NODE_ENV'),
            'debug' => Environment::isDebug(),
            'is_development' => Environment::isDevelopment(),
            'is_production' => Environment::isProduction(),
            'is_webpack_request' => $request->isFromWebpackDevServer(),
            'timestamp' => time(),
        ];

        return new JsonResponse($info, HttpStatusCode::HTTP_OK);
    }
}