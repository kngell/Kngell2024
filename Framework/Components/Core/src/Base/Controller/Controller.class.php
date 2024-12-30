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
    private ViewInterface $view;

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

    public function render(string $templatePath, array $context = []) : string
    {
        $pathParts = explode(DS, $templatePath);
        if (count($pathParts) === 1) {
            $templatePath = strtolower(str_replace('Controller', '', $this::class) . DS . $templatePath);
        }
        $context = array_merge($context, (new NavbarDecorator($this))->page(), ['message' => $this->flash->get()]);
        return $this->view->render($templatePath, $context);
    }

    public function redirect(string $url, bool $permanent = true) : Response
    {
        // $this->session->delete(PREVIOUS_PAGE);
        // $s = $_SESSION;
        // $statusCode = $permanent ? HttpStatusCode::HTTP_SEE_OTHER : HttpStatusCode::HTTP_MOVED_PERMANENTLY;
        // $this->response->setStatusCode($statusCode);
        // $this->response->redirect($url);
        return Rooter::redirect($url, $permanent);
    }

    public function page() : array
    {
        return [];
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

    protected function pageTitle(string $title) : void
    {
        $this->view->pageTitle($title);
    }

    protected function addProperties(array $props) : void
    {
        $this->view->addProperties($props);
    }

    protected function setLayout(string $layout) : void
    {
        $this->view->setLayout($layout);
    }

    protected function response(string $template, array $data = []) : Response
    {
        return new Response(
            $this->render(
                $template,
                $data
            ),
            HttpStatusCode::HTTP_OK,
            ['Content-Type' => 'text/html']
        );
    }

    protected function jsonResponse(string|object|array|bool $data = []) : JsonResponse
    {
        return new JsonResponse($data, HttpStatusCode::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}