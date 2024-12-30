<?php

declare(strict_types=1);

class UserSessionModel extends Model
{
    public function __construct(EntityManagerInterface $em, private TokenInterface $token, private SessionInterface $session)
    {
        parent::__construct($em);
    }

    public function rememberLogin(Users $user, CookieInterface $cookie) : array
    {
        $result = $this->save([
            'token_hash' => $this->token->getRememberHash(),
            'user_id' => $user->getUserId(),
            'expires_at' => date('Y-m-d H:i:s', $cookie->getExpiry()),
            'user_agent' => $this->session->uagent_no_version(),
        ]);
        return [$result, $this->token->getValue()];
    }

    public function getByHash(string $hash) : Users|bool
    {
        $this->entityManager->createQueryBuilder()->select()
            ->from('users')
            ->innerJoin('user_session')
            ->on('users.user_id', 'user_session.user_id')
            ->where('user_session.token_hash', $hash)
            ->build();
        $qr = $this->entityManager->persist()->getResults();
        if ($qr->getQueryResult() && $qr->count() === 0) {
            return false;
        }
        return $qr->getResults('class', 'Users')->single();
    }
}
