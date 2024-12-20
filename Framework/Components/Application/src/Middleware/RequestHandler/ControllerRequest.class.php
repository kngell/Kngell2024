<?php

declare(strict_types=1);

class ControllerRequest implements RequestHandlerInterface
{
    private const array ERRORS_URL = ['/_404_error', '/_500_error'];

    public function __construct(private RouteInfo $route, private Controller $controller, private array $arguments, private string $url)
    {
    }

    public function handle(Request $request): string|Response
    {
        if (in_array($this->url, self::ERRORS_URL)) {
            return $this->route->getMethod()->invoke($this->controller, $this->arguments);
        } else {
            return $this->route->getMethod()->invoke($this->controller, ...$this->arguments);
        }
    }
}