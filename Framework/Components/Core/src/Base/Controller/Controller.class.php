<?php

declare(strict_types=1);
abstract class Controller
{
    use ControllerGettersAndSetters;
    protected Request $request;
    protected Response $response;
    protected TokenInterface $token;
    protected FlashInterface $flash;
    protected SessionInterface $session;
    protected CacheInterface $cache;
    protected CookieInterface $cookie;
    protected EventManagerInterface $eventManager;
    protected HtmlBuilder $builder;
    private ViewInterface $view;
    private string $layout;
    private Model|null $currentModel;

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
        $pathParts = explode(DS, $templatePath);
        if (count($pathParts) === 1) {
            $templatePath = strtolower(str_replace('Controller', '', $this::class) . DS . $templatePath);
        }
        return $this->view->render($templatePath, $this->context($context));
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
     * @return Model
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws DependencyHasNoDefaultValueException
     * @throws BaseInvalidArgumentException
     */
    public function getModel(string $modelName): Model
    {
        if (! class_exists($modelName)) {
            throw new BaseInvalidArgumentException("Model $modelName does not exist.");
        }
        if (! is_subclass_of($modelName, Model::class)) {
            throw new BaseInvalidArgumentException("Model $modelName must extend Model.");
        }
        if (isset($this->currentModel) && $this->currentModel !== null && get_class($this->currentModel) === $modelName) {
            return $this->currentModel;
        }
        return App::diget($modelName);
    }

    /**
     * @param EventManagerInterface $eventManager
     * @return Controller
     */
    public function setEventManager(EventManagerInterface $eventManager): self
    {
        $this->eventManager = $eventManager;
        return $this;
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

    protected function deleteFiles(string $dir): void
    {
        $files = FileManager::dirFilePaths($dir);
        foreach ($files as $file) {
            FileManager::deleteFile($file);
        }
    }

    protected function form(AbstractFormCreator $frm, string $action, array|Entity|bool $formValues = [], array|Entity|bool $formErrors = []): string
    {
        if ($this->session->exists('form')) {
            $form = $this->session->get('form');
            $this->session->delete('form');
        } else {
            $form = $frm->make($action, $formValues, $formErrors);
        }
        return $form;
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
        $this->view->setRequest($this->request);
        if (isset($this->layout)) {
            $this->view->setLayout($this->layout);
        }
        if (str_starts_with($this::class, 'Ecommerce')) {
            $this->setLayout('ecommerce');
        }
        $navbar = match (true) {
            $this->view->getLayout() === 'default' => DefaultNavbarDecorator::class,
            // $this->view->getLayout() === 'admin' => AdminNavbarDecorator::class,
            default => '',
        };
        return array_merge($context, ['message' => $this->flash->get()], ! empty($navbar) ? (new $navbar($this))->page() : []);
    }
}