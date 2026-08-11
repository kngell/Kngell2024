<?php

declare(strict_types=1);
class AccountController extends Controller
{
    public function __construct(private UserModel $user)
    {
    }

    public function index() : Response
    {
        $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
        preg_match_all($pattern, urldecode($this->request->getRequestedUri()), $matches);
        if (isset($matches[0][0])) {
            $user = $this->user->all(['email' => $matches[0][0]])->getResults('class')->single();
            return $this->jsonResponse(! $user);
        }
        return $this->jsonResponse(true);
    }
}
