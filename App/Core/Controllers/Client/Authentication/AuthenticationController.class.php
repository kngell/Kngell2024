<?php

declare(strict_types=1);

abstract class AuthenticationController extends Controller
{
    protected SessionInterface $session;

    public function __construct(protected UsersModel $users, protected HashInterface $hash, protected FlashInterface $flash)
    {
        $this->session = $this->flash->getSession();
    }

    public function authenticate(array $userData) : Users|bool
    {
        $user = $this->users->getByEmail($userData['email']);
        if ($user && $this->hash->passwordCheck($userData['password'], $user->getPassword())) {
            return $user;
        }
        return false;
    }

    public function loginUser(Users $user) : bool
    {
        if (! $this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $this->session->set(CURRENT_USER_SESSION_NAME, [
                'id' => $user->getUserId(),
                'acl' => $user->getAcl(),
                'verified' => $user->isVerified(),
            ]);
            $this->flash->add('You have successfully logged In');
            return true;
        }
        return false;
    }
}