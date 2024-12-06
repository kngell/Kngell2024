<?php

declare(strict_types=1);
class App extends AbstracApp
{
    private RooterInterface $rooter;

    public function __construct()
    {
        AppConstants::enable();
        $this->appConfig = AppConfigSetup::getInstance()->create();
        ContainerClassRegistrator::register($this);
        $this->rooter = $this->get(RooterInterface::class);
        $this->request = $this->get(Request::class);
    }

    public function boot() : self
    {
        $this->loadErrorHandlers();
        $this->phpVersion();
        $this->loadEnvironment();
        $this->loadCache();
        $this->loadSession();
        $this->loadCookies();
        return $this;
    }

    public function run(string $url = '', array $params = []) : void
    {
        $response = $this->rooter->handle($this->request, $this, $url, $params);
        $response->prepare($this->request);
        $response->send();
    }

    public function runError(string $url, array $params) : void
    {
        $this->run($url, $params);
    }
}
