<?php

declare(strict_types=1);
class App extends AbstractApp
{
    public function __construct()
    {
        AppConstants::enable();
        ContainerClassRegistrator::register($this);
    }

    public function boot() : self
    {
        $this->createAppProperties();
        $this->loadErrorHandlers();
        $this->loadSession();
        $this->phpVersion();
        $this->loadEnvironment();
        $this->loadCache();
        $this->loadCookies();
        return $this;
    }

    public function run(string $url = '', array $params = []) : void
    {
        $response = $this->rooter->handle($this->request, $this, $url, $params);
        $response->prepare($this->request);
        $response->send();
    }

    public function runError(string $url, array $params = []) : void
    {
        $this->run($url, $params);
    }
}
