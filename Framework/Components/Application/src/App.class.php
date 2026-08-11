<?php

declare(strict_types=1);

class App extends AbstractApp
{
    public function __construct()
    {
        parent::__construct();
        $this->isCli = php_sapi_name() === 'cli';
        AppConstants::enable();
        $this->appConfig = AppConfig::getInstance()->setup();
        $this->registerCoreBindings();
        ContainerClassRegistrator::register($this);
    }

    public function boot(): self
    {
        $this->loadErrorHandlers();
        $this->phpVersion();
        $this->loadEnvironment();
        $this->loadCache();

        if (!$this->isCli) {
            $this->loadSession();
            $this->loadCookies();
            $this->createAppProperties();
        } else {
            $this->createCliProperties();
        }
        ContainerRegistry::setContainer($this);
        return $this;
    }

    public function run(string $url = '', array $params = []): void
    {
        if ($this->isCli) {
            throw new LogicException('Cannot run HTTP router in CLI mode.');
        }

        $response = $this->rooter->handle($this->request, $this, $url, $params);
        $response->prepare($this->request);
        $response->send();
        // exit;
    }

    public function runError(string $url, array $params = []): void
    {
        $this->run($url, $params);
    }

    public function isFullyBooted(): bool
    {
        foreach ($this->bootMap as $boot => $value) {
            if ($value === false) {
                return false;
            }
        }
        return true;
    }

    public function reBoot(): void
    {
        foreach ($this->bootOrder as $step) {
            if ($this->bootMap[$step] === false) {
                if ($step === 'loadSession' && $this->isCli) {
                    continue;
                }
                $this->$step();
            }
        }
    }

    public function isCli(): bool
    {
        return $this->isCli;
    }
}