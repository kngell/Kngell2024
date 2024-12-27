<?php

declare(strict_types=1);

class UsersModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getUser(string|int $id) : Users|bool
    {
        $user = $this->find($id);
        if ($user->getQueryResult() && $user->count() === 0) {
            return false;
        }
        return $user->getResults('class')->single();
    }

    public function getByEmail(string $email) : Users|bool
    {
        $user = $this->all(['email' => $email]);
        if ($user->getQueryResult() && $user->count() === 0) {
            return false;
        }
        return $user->getResults('class')->single();
    }
}
