<?php

declare(strict_types=1);
abstract class Controller
{
    use ControllerGettersAndSetters;
    use ValidationTrait;

    protected Request $request;
    protected Response $response;
    protected TokenInterface $token;
    protected FlashInterface $flash;
    protected SessionInterface $session;
    protected CacheInterface $cache;
    protected CookieInterface $cookie;
    protected EventManagerInterface $eventManager;
    protected HtmlBuilder $builder;
    protected AbstractFormCreator $frm;
    protected RegionContextInterface $region;
    protected TranslatorServiceInterface $translator;
    protected NavigationHistoryService $navigationHistory;
    protected SectionProviderFactory $providerFactory;
    protected DecoratorFactory $decoratorFactory;
    protected HtmlRegularSectionManager $sectionManager;
    private ViewInterface $view;
    private string $layout;
    private Model|null $currentModel;

    public function __call(string $name, mixed $args): void
    {
        $method = $name;

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new Exception("Method $method not found in controller " . get_class($this));
        }
    }

    public function render(string $templatePath, array $context = []): string
    {
        try {
            $templatePath = $this->templatePath($templatePath);
            $this->view->setRequest($this->request);
            return $this->view->render($templatePath, $this->context($context));
        } catch (AmbiguousViewException | ViewNotFoundException $e) {
            return $this->handleViewError($e, $templatePath);
        }
    }

    public function redirect(string $url, bool $permanent = true): Response
    {
        // $this->session->delete(PREVIOUS_PAGE);
        // $s = $_SESSION;
        // $statusCode = $permanent ? HttpStatusCode::HTTP_SEE_OTHER : HttpStatusCode::HTTP_MOVED_PERMANENTLY;
        // $this->response->setStatusCode($statusCode);
        // $this->response->redirect($url);
        return Rooter::redirect($url, $permanent);
    }

    public function page(): array
    {
        return [];
    }

    /**
     * @param string $modelName
     *
     * @throws BaseInvalidArgumentException
     * @throws BindingResolutionException
     * @throws DependencyHasNoDefaultValueException
     * @throws ReflectionException
     *
     * @return Model
     */
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

    /**
     * @param EventManagerInterface $eventManager
     *
     * @return Controller
     */
    public function setEventManager(EventManagerInterface $eventManager): self
    {
        $this->eventManager = $eventManager;
        return $this;
    }

    protected function decorate(
        string $decoratorClass,
        self|AbstractHtmlDecorator $target,
        array $params = [],
    ): AbstractHtmlDecorator {
        return $this->decoratorFactory->create($decoratorClass, $target, $params);
    }

    protected function redirectWithError(string $message, ?string $redirectUrl = null): Response
    {
        $this->flash->add($message, FlashType::DANGER);

        $this->navigationHistory->markCurrentUrlAsInvalid();
        $targetUrl = $redirectUrl ?? $this->navigationHistory->getRedirectUrl();

        return $this->redirect($targetUrl);
    }

    protected function getRedirectUrl(): string|null
    {
        return $this->navigationHistory->getRedirectUrl();
    }

    protected function getFlashData(string $action): array
    {
        $flashedData = $this->flash->getFormData($action);
        $values = $flashedData['values'];
        $errors = $flashedData['errors'];
        $files = $flashedData['files'];
        return [$values, $errors, $files];
    }

    protected function form(string $action, array|Entity &$formValues = [], array &$formErrors = [], array &$files = []): string
    {
        $flashedData = $this->flash->getFormData($action);
        $values = !empty($flashedData['values']) ? $flashedData['values'] : $formValues;
        $errors = !empty($flashedData['errors']) ? $flashedData['errors'] : $formErrors;
        $files = !empty($flashedData['files']) ? $flashedData['files'] : $files;
        return $this->frm->make($action, $values, $errors, $files);
    }

    protected function formatBytes(int $bytes): string
    {
        return $this->view->formatBytes($bytes);
    }

    /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function before()
    {
    }

    /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function after()
    {
    }

    protected function pageTitle(string $title): void
    {
        $this->view->pageTitle($title);
    }

    protected function addProperties(array $props): void
    {
        $this->view->addProperties($props);
    }

    protected function response(string $template, array $data = []): Response
    {
        return new Response(
            $this->render(
                $template,
                $data,
            ),
            HttpStatusCode::HTTP_OK,
            ['Content-Type' => 'text/html'],
        );
    }

    protected function jsonResponse(string|object|array|bool $data = []): JsonResponse
    {
        $response = new JsonResponse($data, HttpStatusCode::HTTP_OK, ['Content-Type' => 'application/json']);
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
        $this->view->setToken($this->token);
        if (isset($this->layout)) {
            $this->view->setLayout($this->layout);
        }
        $navbar = match (true) {
            $this->view->getLayout() === 'default' => DefaultNavbarDecorator::class,
            $this->view->getLayout() === 'admin' => AdminNavbarDecorator::class,
            default => '',
        };
        return array_merge($context, ['message' => $this->flash->get()], !empty($navbar) ? (new $navbar($this))->page() : []);
    }
}