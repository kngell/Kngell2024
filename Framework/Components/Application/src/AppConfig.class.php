<?php

declare(strict_types=1);
final class AppConfig
{
    use BootstrapTrait;
    use AppConfigGettersAndSettersTrait;

    public const APP_MIN_VERSION = '8.2.11';
    public const APP_CORE_VERSION = '1.0.0';

    protected array$errorHandling = [];
    protected ?int $errorLevel = null;
    protected array $config = [];
    protected array $session;
    protected ?string $newSessionDriver = null;
    protected bool $isSessionGlobal = false;
    protected ?string $globalSessionKey = null;
    protected array $cookie = [];
    protected array $cache = [];
    protected bool $isCacheGlobal = false;
    protected ?string $globalCacheKey = null;
    protected ?string $newCacheDriver;
    protected array $routes = [];
    protected string|null $routeHandler;
    protected string|null $newRouter;
    protected array $containerProviders = [];

    /** @var AppConfig */
    private static $instance;

    private function __construct()
    {
    }

    final public function setup() : self
    {
        $this->setConfig(YamlFile::get('app'))
            ->setErrorHandler(E_ALL)
            ->setSession(null, true)
            ->setCookie(CookieConfig::baseConfig())
            ->setCache(null, true)
            ->setRoutes(YamlFile::get('routes'))
            ->setContainerProviders(YamlFile::get('providers'));
        return $this;
    }

    public static function getInstance() : self
    {
        if (! isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Set the application main configuration from the project app.yml file.
     *
     * @param array $ymlApp
     * @return self
     */
    public function setConfig(array $ymlApp): self
    {
        $this->config = $ymlApp;
        return $this;
    }

    /**
     * Undocumented function.
     *
     * @param string $errorClass
     * @param mixed $level
     * @return self
     */
    public function setErrorHandler(mixed $level = null): self
    {
        $this->errorHandling = $this->config['error_handler']; //$errorHandling;
        $this->errorLevel = $level;
        return $this;
    }

    /**
     * Set the application session configuration from the session.yml file else
     * load the core session configration class.
     *
     * @param string|null $newSessionDriver
     * @param bool $isGlobal
     * @param null|string $globalKey
     * @return AppConfigSetup
     * @throws BaseInvalidArgumentException
     */
    public function setSession(string|null $newSessionDriver = null, bool $isGlobal = false, ?string $globalKey = null): self
    {
        $this->session = ! empty($this->config['session']) ? $this->config['session'] : SessionConfig::baseConfiguration();
        $this->newSessionDriver = ($newSessionDriver !== null) ? $newSessionDriver : $this->getDefaultSessionDriver();
        $this->isSessionGlobal = $isGlobal;
        $this->globalSessionKey = $globalKey;
        return $this;
    }

    /**
     * et the application cookie configuration from the session.yml file.
     * @param array $ymlCookie
     * @return AppConfig
     */
    public function setCookie(array $ymlCookie = []): self
    {
        $this->cookie = $ymlCookie;
        return $this;
    }

    /**
     * Set the application cache configuration from the session.yml file.
     * @param array $ymlCache
     * @param string|null $newCacheDriver
     * @param bool $isGloabl
     * @param string|null $globalKey
     * @return $this
     */
    public function setCache(?string $newCacheDriver = null, bool $isGloabl = false, ?string $globalKey = null): self
    {
        $this->cache = (! empty($this->config['cache']) ? $this->config['cache'] : (new CacheConfig)->baseConfiguration());
        $this->newCacheDriver = ($newCacheDriver !== null) ? $newCacheDriver : $this->getDefaultCacheDriver();
        $this->isCacheGlobal = $isGloabl;
        $this->globalCacheKey = $globalKey;
        return $this;
    }

    /**
     * Set the application routes configuration from the session.yml file.
     *
     * @param array $ymlRoutes
     * @param string|null $routeHandler
     * @param string|null $newRouter - accepts the fully qualified namespace of new router class
     * @return selfs
     */
    public function setRoutes(array $ymlRoutes, string|null $routeHandler = null, string|null $newRouter = null): self
    {
        $this->routes = $ymlRoutes;
        $this->routeHandler = ($routeHandler !== null) ? $routeHandler : $this->defaultRouteHandler();
        $this->newRouter = ($newRouter !== null) ? $newRouter : '';
        return $this;
    }

    /**
     * Set the application container providers configuration from the session.yml file.
     *
     * @param array $ymlProviders
     * @return self
     */
    public function setContainerProviders(array $ymlProviders): self
    {
        $this->containerProviders = $ymlProviders;
        return $this;
    }

    /**
     * Turn on global session from public/index.php bootstrap file to make the session
     * object available globally throughout the application using the GlobalManager object.
     * @return bool
     */
    public function isSessionGlobal(): bool
    {
        return isset($this->isSessionGlobal) && $this->isSessionGlobal === true ? true : false;
    }

    /**
     * Turn on global caching from public/index.php bootstrap file to make the cache
     * object available globally throughout the application using the GlobalManager object.
     * @return bool
     */
    public function isCacheGlobal(): bool
    {
        return isset($this->isCacheGlobal) && $this->isCacheGlobal === true ? true : false;
    }

    /**
     * Returns the default route handler mechanism.
     *
     * @return string
     */
    protected function defaultRouteHandler(): string
    {
        return '/'; //$this->request->getPath();
    }

    /**
     * Get the default cache driver defined with the cache.yml file.
     *
     * @return string
     */
    protected function getDefaultCacheDriver(): string
    {
        return $this->getDefaultSettings($this->getCache());
    }

    protected function getDefaultSessionDriver(): string
    {
        return $this->getDefaultSettings($this->getSession());
    }
}
