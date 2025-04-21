<?php

declare(strict_types=1);

trait ControllerGettersAndSetters
{
    /**
     * @param Request $request
     * @return Controller
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param ViewInterface $view
     * @return Controller
     */
    public function setView(ViewInterface $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @param Response $response
     * @return Controller
     */
    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @param TokenInterface $token
     * @return Controller
     */
    public function setToken(TokenInterface $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @param FlashInterface $flash
     * @return Controller
     */
    public function setFlash(FlashInterface $flash): self
    {
        $this->flash = $flash;
        return $this;
    }

    /**
     * @param SessionInterface $session
     * @return Controller
     */
    public function setSession(SessionInterface $session): self
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
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

    /**
     * @return HtmlBuilder
     */
    public function getBuilder(): HtmlBuilder
    {
        return $this->builder;
    }

    /**
     * @param HtmlBuilder $builder
     * @return Controller
     */
    public function setBuilder(HtmlBuilder $builder): self
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface
    {
        return $this->token;
    }

    /**
     * @return Model
     */
    public function getCurrentModel(): Model
    {
        return $this->currentModel;
    }

    protected function setLayout(string $layout) : void
    {
        $this->layout = $layout;
    }

    /**
     * @param Model $model
     * @return void
     */
    protected function currentModel(Model $model) : void
    {
        $this->currentModel = $model;
    }
}