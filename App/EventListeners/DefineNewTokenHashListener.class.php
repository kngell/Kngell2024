<?php

declare(strict_types=1);

class DefineNewTokenHashListener implements EventListenerInterface
{
    public function update(EventInterface $event): ?object
    {
        $object = $event->getObject();
        if (! $object instanceof LogoutController) {
            return new NullEvent();
        }
        $result = $this->deleteOldTokenHash($object);
        if (! $result instanceof NullObjectInterface && $result->getQueryResult() && $result->rowCount()) {
            $this->setNewTokenHash(AuthService::currentUser(), $object);
        }
        return $event;
    }

    private function setNewTokenHash(User $user, LogoutController $logout) : void
    {
        list($result, $token) = $logout->getUserSession()->rememberLogin($user, $logout->getCookie());
        if ($result->getQueryResult() && $result->getLastInsertId()) {
            $logout->getCookie()->set($token, REMEMBER_ME_COOKIE_NAME);
        }
    }

    private function deleteOldTokenHash(LogoutController $logout) : QueryResult|NullObjectInterface
    {
        $cookie = $logout->getCookie();
        if ($cookie->exists(REMEMBER_ME_COOKIE_NAME)) {
            $old_cookie = $cookie->get(REMEMBER_ME_COOKIE_NAME);
            $cookie->delete(REMEMBER_ME_COOKIE_NAME);
            return $logout->getUserSession()->delete(['token_hash' => (new Token($old_cookie))->getRememberHash()]);
        }
        return new NullObject();
    }
}