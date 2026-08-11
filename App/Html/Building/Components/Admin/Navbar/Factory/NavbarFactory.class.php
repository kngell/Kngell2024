<?php

declare(strict_types=1);

final class NavbarFactory
{
    /** @var array<NavbarType, callable> */
    private array $builders = [];

    public function __construct(
        private AdminNavSectionProvider $provider,
        private readonly UserContext $userContext,
    ) {
        $this->register(NavbarType::DEFAULT, function (Controller $controller) {
            return new DefaultNavbarDecorator($controller, $this->userContext);
        });
        $this->register(NavbarType::ECOMMERCE, function (Controller $controller) {
            return new DefaultNavbarDecorator($controller, $this->userContext);
        });

        $this->register(NavbarType::ADMIN, function (Controller $controller) {
            return new AdminNavbarDecorator($this->provider, $controller);
        });
    }

    public function register(NavbarType $type, callable $builder): self
    {
        $this->builders[$type->value] = $builder;
        return $this;
    }

    public function create(NavbarType $type, Controller $controller): AbstractHtmlDecorator
    {
        if (!isset($this->builders[$type->value])) {
            throw new InvalidArgumentException(
                sprintf('No builder registered for navbar type "%s"', $type->value),
            );
        }

        return $this->builders[$type->value]($controller);
    }

    // Convenience methods
    public function createDefault(Controller $controller): DefaultNavbarDecorator
    {
        $decorator = $this->create(NavbarType::DEFAULT, $controller);

        if (!$decorator instanceof DefaultNavbarDecorator) {
            throw new RuntimeException('Expected DefaultNavbarDecorator');
        }

        return $decorator;
    }

    public function createAdmin(Controller $controller): AdminNavbarDecorator
    {
        $decorator = $this->create(NavbarType::ADMIN, $controller);

        if (!$decorator instanceof AdminNavbarDecorator) {
            throw new RuntimeException('Expected AdminNavbarDecorator');
        }

        return $decorator;
    }
}