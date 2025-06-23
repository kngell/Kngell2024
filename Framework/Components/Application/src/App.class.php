<?php

declare(strict_types=1);
class App extends AbstractApp
{
    public function __construct()
    {
        // Initialize Container properties since we can't call parent::__construct()
        $this->resolutionContext = new ResolutionContext();
        $this->registerCoreBindings();

        AppConstants::enable();
        $this->appConfig = AppConfig::getInstance()->setup();
        ContainerClassRegistrator::register($this);
    }

    public function boot() : self
    {
        $this->loadErrorHandlers();
        $this->loadSession();
        $this->phpVersion();
        $this->loadEnvironment();
        $this->loadCache();
        $this->loadCookies();
        $this->createAppProperties();
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