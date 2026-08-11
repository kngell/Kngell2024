<?php

declare(strict_types=1);

class TokenResourceController extends Controller
{
    public function __construct(private readonly TokenInterface $token)
    {
        parent::__construct();
    }

    public function provideToken(): Response
    {
        $isAjax = $this->request->isAjax();
        if ($isAjax) {
            return $this->respondSuccess(
                isAjax: $isAjax,
                message: 'csrfToken provided',
                flashType: FlashType::SUCCESS,
                extraData: [
                    'token' => $this->token->getCsrfHash(),
                ],
                redirect: $this->getRedirectUrl(),
            );
        }
        return $this->redirect($this->getRedirectUrl());
    }
}