<?php

declare(strict_types=1);
class LogoutController extends Controller
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function index() : Response
    {
        $s = $_SESSION;
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $this->session->invalidate();
        }
        return $this->redirect('/');
    }
}
