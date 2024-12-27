<?php

declare(strict_types=1);

class GrantAccessMiddleware implements MiddlewareInterface
{
    private array $acl;
    private array $menuItems;

    public function __construct(private SessionInterface $session)
    {
        $this->acl = json_decode(file_get_contents(FileManager::get(APP, 'acl.json')), true);
        $this->menuItems
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $aclGroup = $this->acl;
        return $next->handle($request);
    }
}