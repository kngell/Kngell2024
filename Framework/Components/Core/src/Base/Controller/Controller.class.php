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
    private ViewInterface $view;
    private string $layout;
    private Model|null $currentModel;
    private NavigationHistoryService $navigationHistory;

    public function __call($name, $args)
    {
        $method = $name . 'Action';

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
            $pathParts = explode(DS, $templatePath);
            if (count($pathParts) === 1) {
                $templatePath = strtolower(str_replace('Controller', '', $this::class) . DS . $templatePath);
            }
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

    protected function redirectWithError(string $message, ?string $redirectUrl = null): Response
    {
        $this->flash->add($message, FlashType::DANGER);

        $this->navigationHistory->markCurrentUrlAsInvalid();
        $targetUrl = $redirectUrl ?? $this->navigationHistory->getRedirectUrl();

        return $this->redirect($targetUrl);
    }

    protected function handleViewError(Exception $e, string $viewPath): string
    {
        if ($e instanceof AmbiguousViewException) {
            // Log the ambiguity for debugging
            error_log('View ambiguity in ' . static::class . ": {$e->getMessage()}");

            // In development, show helpful error
            if ($_ENV['APP_ENV'] === 'development') {
                return "<div style='background: #ffebee; padding: 20px; border: 1px solid #c62828;'>
                    <h3>Ambiguous View Reference</h3>
                    <p><strong>Controller:</strong> " . static::class . "</p>
                    <p><strong>View path:</strong> {$viewPath}</p>
                    <p><strong>Error:</strong> {$e->getMessage()}</p>
                    <p>Please specify the full path to disambiguate the view.</p>
                </div>";
            }

            // In production, show generic error
            return $this->render('errors/500', ['message' => 'View configuration error']);
        }

        if ($e instanceof ViewNotFoundException) {
            // Let your error controller handle 404s
            throw $e;
        }

        // Re-throw other exceptions
        throw $e;
    }

    protected function getRedirectUrl(): string|null
    {
        if ($this->session->exists('current_url')) {
            $previousUrl = $this->session->get('current_url');
            $this->session->delete('current_url');
            return $previousUrl;
        }
        return $this->session->get('previous_url');
    }

    protected function form(string $action, array|Entity &$formValues = [], array &$formErrors = [], array &$files = []): string
    {
        $flashedData = $this->flash->flushForm($action);
        $values = !empty($flashedData['values']) ? $flashedData['values'] : $formValues;
        $errors = !empty($flashedData['errors']) ? $flashedData['errors'] : $formErrors;
        $files = !empty($flashedData['files']) ? $flashedData['files'] : [];
        // Make sure files are passed to the form instance
        return $this->frm->make($action, $values, $errors, $files);
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
        return new JsonResponse($data, HttpStatusCode::HTTP_OK, ['Content-Type' => 'application/json']);
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