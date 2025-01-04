<?php

declare(strict_types=1);

class UserSessionModel extends Model
{
    public function __construct(EntityManagerInterface $em, private TokenInterface $token, private SessionInterface $session, private CookieInterface $cookie)
    {
        parent::__construct($em);
    }

    public function rememberLogin(User $user) : array
    {
        /** @var QueryResult */
        $result = $this->save([
            'token_hash' => $this->token->getRememberHash(),
            'user_id' => $user->getUserId(),
            'expires_at' => date('Y-m-d H:i:s', $this->cookie->getExpiry()),
            'user_agent' => $this->session->uagent_no_version(),
        ]);
        return [$result, $this->token->getValue()];
    }

    public function getByHash(string $hash) : array|bool
    {
        $this->entityManager->createQueryBuilder()->select()
            ->innerJoin('user', '*')
            ->on('user.user_id', 'user_session.user_id')
            ->where('user_session.token_hash', $hash)
            ->build();
        $qr = $this->entityManager->persist()->getResults();
        if ($qr->getQueryResult() && $qr->count() === 0) {
            return false;
        }
        $results = $qr->getResults()->first();
        return[(new User)->assign($results),  $this->hasExprired((new UserSession)->assign($results))];
    }

    public function hasExprired(UserSession $userSession) : bool
    {
        return strtotime($userSession->getExpiresAt()) < time();
    }
}