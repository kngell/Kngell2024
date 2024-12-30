<?php

declare(strict_types=1);
class LogoutController extends Controller
{
    public function index() : Response
    {
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $this->session->invalidate();
        }
        $previousPage = $this->session->get(PREVIOUS_PAGE);
        return $this->redirect($previousPage ?? '/');
    }
}