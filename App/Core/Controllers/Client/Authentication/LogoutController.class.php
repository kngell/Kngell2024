<?php

declare(strict_types=1);
class LogoutController extends AuthController
{
    private int $userId;

    public function index() : Response
    {
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $previousPage = $this->getRedirectUrl();
            $this->userId = $this->session->get(CURRENT_USER_SESSION_NAME);
            $this->session->invalidate();
        }
        $event = $this->eventManager->notify(LogoutEvent::class, $this);
        $cartData = $event->getResults();
        // if (! empty($cartData)) {
        //     $previousPage = $previousPage . '?cart=' . $cartData;
        // }
        return new RedirectResponse($previousPage ?? '/');
    }

    /**
     * Get the value of userId.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}