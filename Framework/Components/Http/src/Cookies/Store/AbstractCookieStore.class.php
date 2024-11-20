<?php

declare(strict_types=1);

abstract class AbstractCookieStore implements CookieStoreInterface
{
    /** @var CookieEnvironment */
    protected CookieEnvironment $cookieEnvironment;
    protected SuperGlobalsInterface $gv;

    /**
     * Main class constructor.
     *
     * @param CookieEnvironment $cookieEnvironment
     */
    public function __construct(CookieEnvironment $cookieEnvironment, SuperGlobalsInterface $gv)
    {
        $this->cookieEnvironment = $cookieEnvironment;
        $this->gv = $gv;
    }
}