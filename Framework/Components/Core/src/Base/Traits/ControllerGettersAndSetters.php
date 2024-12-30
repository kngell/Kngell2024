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
}