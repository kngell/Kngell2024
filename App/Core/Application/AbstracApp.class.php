<?php

declare(strict_types=1);
abstract class AbstracApp extends Container
{
    protected AppConfigSetup $appConfig;

    public function app(): App
    {
        return self::getInstance();
    }

    public static function diGet(string $class, array $args = []): mixed
    {
        return self::getInstance()->get($class, $args);
    }

    /**
     * @return string
     * @throws BaseLengthException
     */
    public function getGlobalCacheKey(): string
    {
        if ($this->appConfig->getGlobalCacheKey() !== null && strlen($this->appConfig->getGlobalCacheKey()) < 3) {
            throw new BaseLengthException($this->appConfig->getGlobalCacheKey() . ' is invalid this needs to be more than 3 characters long');
        }
        return ($this->appConfig->getGlobalCacheKey() !== null) ? $this->appConfig->getGlobalCacheKey() : 'cache_global';
    }

    /**
     * @return string
     * @throws BaseLengthException
     */
    public function getGlobalSessionKey(): string
    {
        if ($this->appConfig->getGlobalSessionKey() !== null && strlen($this->appConfig->getGlobalSessionKey()) < 3) {
            throw new BaseLengthException($this->appConfig->getGlobalSessionKey() . ' is invalid this needs to be more than 3 characters long');
        }
        return ($this->appConfig->getGlobalSessionKey() !== null) ? $this->appConfig->getGlobalSessionKey() : 'session_global';
    }

    /**
     * Compare PHP version with the core version ensuring the correct version of
     * PHP and MagmaCore framework is being used at all time in sync.
     *
     * @return void
     */
    protected function phpVersion(): void
    {
        if (version_compare($phpVersion = PHP_VERSION, $coreVersion = $this->appConfig->getConfig()['app']['app_version'], '<')) {
            die(sprintf('You are runninig PHP %s, but the core framework requires at least PHP %s', $phpVersion, $coreVersion));
        }
    }

    /**
     * Load the framework default enviornment configuration. Most configurations
     * can be done from inside the app.yml file.
     *
     * @return void
     */
    protected function loadEnvironment(): void
    {
        $settings = $this->appConfig->getConfig()['settings'];
        ini_set('default_charset', $settings['default_charset']);
        date_default_timezone_set($settings['default_timezone']);
    }

    protected function loadErrorHandlers(): void
    {
        error_reporting($this->appConfig->getErrorHandlerLevel());
        set_error_handler($this->appConfig->getErrorHandling()['error']);
        set_exception_handler($this->appConfig->getErrorHandling()['exception']);
    }

    protected function loadCache(): CacheInterface
    {
        $cache = $this->get(CacheFacade::class)->create($this->appConfig->getCacheIdentifier(), $this->appConfig->getCache());
        if ($this->app()->isCacheGlobal() === true) {
            GlobalManager::set($this->app()->getGlobalCacheKey(), $cache);
        }
        return $cache;
    }

    /**
     * Turn on global caching from public/index.php bootstrap file to make the cache
     * object available globally throughout the application using the GlobalManager object.
     * @return bool
     */
    protected function isCacheGlobal(): bool
    {
        return $this->appConfig->isCacheGlobal();
    }

    protected function loadSession(): Object
    {
        $session = $this->get(SessionFacade::class, [
            $this->appConfig->getSession()['session_name'],
            $this->appConfig->getSessionDriver(),
            $this->get(SessionEnvironment::class, $this->appConfig->getSession()),
        ])->setSession();
        if ($this->isSessionGlobal() === true) {
            GlobalManager::set($this->app()->getGlobalSessionKey(), $session);
        }
        return $session;
    }

    /**
     * Turn on global session from public/index.php bootstrap file to make the session
     * object available globally throughout the application using the GlobalManager object.
     * @return bool
     */
    protected function isSessionGlobal(): bool
    {
        return $this->appConfig->isSessionGlobal();
    }

    protected function loadCookies()
    {
        return $this->get(CookieFacade::class, [
            'cookieEnvironmentArray' => [],
            'cookieConfig' => $this->get(CookieConfig::class),
            'gv' => $this->get(SuperGlobalsInterface::class),
        ])->initialize();
    }
}