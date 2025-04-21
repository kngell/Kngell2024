<?php

declare(strict_types=1);
class LogoutController extends AuthController
{
    public function index() : Response
    {
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $previousPage = $this->session->get(PREVIOUS_PAGE);
            $this->session->invalidate();
        }
        $result = $this->deleteOldTokenHash();
        if ($result && $result->getQueryResult() && $result->rowCount()) {
            $this->setNewTokenHash(AuthService::currentUser());
        }
        return $this->redirect($previousPage ?? '/');
    }
}