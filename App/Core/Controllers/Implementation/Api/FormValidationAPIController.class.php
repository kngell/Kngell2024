<?php

declare(strict_types=1);
class FormValidationApiController extends Controller implements JavascriptApiInterface
{
    private const string RULES_PATH = APP . 'Html' . DS . 'Helpers' . DS . 'Forms' . DS . 'Validation Rules' . DS;

    public function __construct(
        private ValidationRulesExporter $exporter,
        private FileSearchManager $searchFile,
    ) {
    }

    public function getSettings(): JsonResponse
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
            $data = $this->exporter->exportForClient($rulesPath);
            return new JsonResponse($data, HttpStatusCode::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to load validation rules: ' . $e->getMessage()],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function getGlobalSettings(): JsonResponse
    {
        try {
            $settings = $this->exporter->getGlobalSettings();
            return new JsonResponse($settings, HttpStatusCode::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to load global settings: ' . $e->getMessage()],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function clearCache(): JsonResponse
    {
        if (!$this->request->isFromWebpackDevServer() && Environment::isProduction()) {
            return new JsonResponse(
                ['error' => 'Cache clearing not allowed in production'],
                HttpStatusCode::HTTP_FORBIDDEN,
            );
        }

        $rulesFile = $this->request->getQuery()->get('rules');

        try {
            if ($rulesFile) {
                $rulesPath = APP . "Forms/{$rulesFile}.yaml";
                $result = $this->exporter->clearRulesCache($rulesPath);
                $message = "Cache cleared for {$rulesFile}";
            } else {
                $result = $this->exporter->clearAllCache();
                $message = 'All validation caches cleared';
            }

            return new JsonResponse([
                'success' => $result,
                'message' => $message,
                'environment' => Environment::get('APP_ENV'),
            ], HttpStatusCode::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to clear cache: ' . $e->getMessage()],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}