<?php

declare(strict_types=1);

class DefaultNavbarDecorator extends AbstractHtmlDecorator
{
    public function __construct(Controller $controller, private readonly UserContext $userContext)
    {
        parent::__construct($controller);
    }

    public function page(): array
    {
        $target = $this->getTarget();

        /** @var NavbarHtmlElement */
        $navElements = new NavbarHtmlElement(
            $target->getBuilder(),
            $target->getSession(),
            $target->request,
            $this->userContext,
        );
        return ['navComponent' => $navElements->display()];
    }
}