<?php

declare(strict_types=1);

abstract class Controller
{
    use ControllerGettersAndSetters;
    use ValidationTrait;
    use AjaxResponseTrait;
    use HtmlPageCacheableTrait;
    use CacheInvalidationTrait;
    use CacheInvalidationAwareTrait;

    public function __construct()
    {
    }

    public function __call(string $name, array $args): mixed
    {
        if (!method_exists($this, $name)) {
            throw new Exception("Method $name not found in controller " . get_class($this));
        }

        if ($this->before() === false) {
            return null;
        }

        $result = call_user_func_array([$this, $name], $args);
        $this->after();

        return $result;
    }

    public function render(string $templatePath, array $context = []): string
    {
        try {
            $this->logTiming('View action started');
            $templatePath = $this->templatePath($templatePath);
            $view = $this->getView();
            $view->setRequest($this->request);

            return $view->render($templatePath, $this->context($context));
        } catch (AmbiguousViewException | ViewNotFoundException $e) {
            return $this->handleViewError($e, $templatePath);
        }
    }

    public function page(): array
    {
        return [];
    }

    public function getModel(string $modelName): Model
    {
        if (!class_exists($modelName)) {
            throw new BaseInvalidArgumentException("Model $modelName does not exist.");
        }
        if (!is_subclass_of($modelName, Model::class)) {
            throw new BaseInvalidArgumentException("Model $modelName must extend Model.");
        }
        if (isset($this->currentModel) && $this->currentModel !== null && get_class($this->currentModel) === $modelName) {
            return $this->currentModel;
        }
        return App::diget($modelName);
    }

    protected function logTiming(string $label): void
    {
        static $startTime = null;
        if ($startTime === null) {
            $startTime = microtime(true);
        }

        $time = (microtime(true) - $startTime) * 1000;
        error_log(sprintf(
            '[TIMING] %-30s: %8.2f ms | Memory: %5.2f MB',
            $label,
            $time,
            memory_get_usage(true) / 1024 / 1024,
        ));
    }

    protected function redirect(string $url, bool $permanent = true): Response
    {
        return Rooter::redirect($url, $permanent);
    }

    protected function getGormConfig(): ?FormConfig
    {
        return null;
    }

    protected function decorate(
        string $decoratorClass,
        self|AbstractHtmlDecorator $target,
        array|object $params = [],
    ): AbstractHtmlDecorator {
        return $this->getDecoratorFactory()->create($decoratorClass, $target, $params);
    }

    protected function redirectWithError(string $message, ?string $redirectUrl = null): Response
    {
        $this->getFlash()->add($message, FlashType::DANGER);
        $this->getNavigationHistory()->markCurrentUrlAsInvalid();
        $targetUrl = $redirectUrl ?? $this->getNavigationHistory()->getRedirectUrl();
        return $this->redirect($targetUrl);
    }

    protected function getRedirectUrl(): string|null
    {
        return $this->getNavigationHistory()->getRedirectUrl();
    }

    protected function getFlashData(string $action): array
    {
        $flash = $this->getFlash();
        $flashedData = $flash->getFormData($action);
        return [
            $flashedData['values'] ?? [],
            $flashedData['errors'] ?? [],
            $flashedData['files'] ?? [],
        ];
    }

    protected function form(string $action, array|Entity &$formValues = [], array &$formErrors = [], array &$files = []): string
    {
        $flash = $this->getFlash();
        $flashedData = $flash->getFormData($action);
        $values = !empty($flashedData['values']) ? $flashedData['values'] : $formValues;
        $errors = !empty($flashedData['errors']) ? $flashedData['errors'] : $formErrors;
        $files = !empty($flashedData['files']) ? $flashedData['files'] : $files;
        return $this->getForm()->make($action, $values, $errors, $files);
    }

    protected function formatBytes(int $bytes): string
    {
        return $this->getView()->formatBytes($bytes);
    }

    protected function before(): bool
    {
        return true;
    }

    protected function after(): void
    {
    }

    protected function pageTitle(string $title): void
    {
        $this->getView()->pageTitle($title);
    }

    protected function addProperties(array $props): void
    {
        $this->getView()->addProperties($props);
    }

    protected function response(string $template, array $data = []): Response
    {
        return new Response(
            $this->render($template, $data),
            HttpStatusCode::HTTP_OK,
            ['Content-Type' => 'text/html'],
        );
    }

    protected function jsonResponse(string|object|array|bool $data = [], HttpStatusCode $statusCode = HttpStatusCode::HTTP_OK): JsonResponse
    {
        $response = new JsonResponse($data, $statusCode, ['Content-Type' => 'application/json']);
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $response;
    }

    private function templatePath(string $templatePath): string
    {
        if (str_starts_with($templatePath, '/')) {
            return ltrim($templatePath, '/');
        }

        if (str_contains($templatePath, '@')) {
            [$module, $path] = explode('@', $templatePath, 2);
            return $module . DS . $path;
        }

        $controllerClass = static::class;
        $controllerPath = str_replace('Controller', '', $controllerClass);
        $controllerPath = StringUtils::kebabCase($controllerPath);
        $controllerPath = str_replace('-', DS, $controllerPath);

        return $controllerPath . DS . trim($templatePath, DS);
    }

    private function context(array $context): array
    {
        $view = $this->getView();
        $view->setToken($this->getToken());

        if (isset($this->layout)) {
            $view->setLayout($this->layout);
        }

        $navbar = $this->getNavBarFactory()->create($view->getLayout(), $this);
        return array_merge(
            $context,
            ['message' => $this->getFlash()->get()],
            !empty($navbar) ? $navbar->page() : [],
        );
    }
}