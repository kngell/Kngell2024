<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

trait ControllerGettersAndSetters
{
    use LazyControllerTrait;

    protected Request $request;
    protected Response $response;
    protected EventDispatcherInterface $eventDispatcher;
    protected AbstractFormCreator $frm;
    private ?Model $currentModel = null;
    private ?NavbarType $layout = null;

    public function setApp(App $app): self
    {
        $this->initializeLazyDependencies($app);
        return $this;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setResponse(Response $response): self
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    // Keep existing setter methods but mark them as optional
    // They can still be called but are not required

    public function setView(ViewInterface $view): self
    {
        // Store in resolved dependencies if already set
        $this->resolvedDependencies[ViewInterface::class] = $view;
        return $this;
    }

    public function setToken(TokenInterface $token): self
    {
        $this->resolvedDependencies[TokenInterface::class] = $token;
        return $this;
    }

    public function setFlash(FlashInterface $flash): self
    {
        $this->resolvedDependencies[FlashInterface::class] = $flash;
        return $this;
    }

    public function setSession(SessionInterface $session): self
    {
        $this->resolvedDependencies[SessionInterface::class] = $session;
        return $this;
    }

    public function setCache(CacheInterface $cache): self
    {
        $this->resolvedDependencies[CacheInterface::class] = $cache;
        return $this;
    }

    public function setCookie(CookieInterface $cookie): self
    {
        $this->resolvedDependencies[CookieInterface::class] = $cookie;
        return $this;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        $this->resolvedDependencies[EventDispatcherInterface::class] = $eventDispatcher;
        return $this;
    }

    public function setBuilder(HtmlBuilder $builder): self
    {
        $this->resolvedDependencies[HtmlBuilder::class] = $builder;
        return $this;
    }

    public function setForm(AbstractFormCreator $frm): self
    {
        $this->resolvedDependencies[AbstractFormCreator::class] = $frm;
        return $this;
    }

    public function setRegion(RegionContextInterface $region): self
    {
        $this->resolvedDependencies[RegionContextInterface::class] = $region;
        return $this;
    }

    public function setTranslator(TranslatorServiceInterface $translator): self
    {
        $this->resolvedDependencies[TranslatorServiceInterface::class] = $translator;
        return $this;
    }

    public function setNavigationHistory(NavigationHistoryService $navigationHistory): self
    {
        $this->resolvedDependencies[NavigationHistoryService::class] = $navigationHistory;
        return $this;
    }

    public function setDecoratorFactory(DecoratorFactory $decoratorFactory): self
    {
        $this->resolvedDependencies[DecoratorFactory::class] = $decoratorFactory;
        return $this;
    }

    public function setSectionManager(HtmlRegularSectionManager $sectionManager): self
    {
        $this->resolvedDependencies[HtmlRegularSectionManager::class] = $sectionManager;
        return $this;
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->resolvedDependencies[LoggerInterface::class] = $logger;
        return $this;
    }

    public function setNavBarFactory(NavbarFactory $navBarFactory): self
    {
        $this->resolvedDependencies[NavbarFactory::class] = $navBarFactory;
        return $this;
    }

    // Lazy getters for all dependencies
    protected function getView(): ViewInterface
    {
        return $this->getLazy(ViewInterface::class);
    }

    protected function getToken(): TokenInterface
    {
        return $this->getLazy(TokenInterface::class);
    }

    protected function getFlash(): FlashInterface
    {
        return $this->getLazy(FlashInterface::class);
    }

    protected function getSession(): SessionInterface
    {
        return $this->getLazy(SessionInterface::class);
    }

    protected function getCache(): CacheInterface
    {
        return $this->getLazy(CacheInterface::class);
    }

    protected function getCookie(): CookieInterface
    {
        return $this->getLazy(CookieInterface::class);
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->getLazy(EventDispatcherInterface::class);
    }

    protected function getBuilder(): HtmlBuilder
    {
        return $this->getLazy(HtmlBuilder::class);
    }

    protected function getForm(): AbstractFormCreator
    {
        return $this->frm;
    }

    protected function getRegion(): RegionContextInterface
    {
        return $this->getLazy(RegionContextInterface::class);
    }

    protected function getTranslator(): TranslatorServiceInterface
    {
        return $this->getLazy(TranslatorServiceInterface::class);
    }

    protected function getNavigationHistory(): NavigationHistoryService
    {
        return $this->getLazy(NavigationHistoryService::class);
    }

    protected function getDecoratorFactory(): DecoratorFactory
    {
        return $this->getLazy(DecoratorFactory::class);
    }

    protected function getSectionManager(): HtmlRegularSectionManager
    {
        return $this->getLazy(HtmlRegularSectionManager::class);
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->getLazy(LoggerInterface::class);
    }

    protected function getNavBarFactory(): NavbarFactory
    {
        return $this->getLazy(NavbarFactory::class);
    }

    protected function layout(NavbarType $layout): void
    {
        $this->layout = $layout;

        // $logger = $this->getLogger();
        // if ($logger instanceof CustomLogger) {
        //     $logger->flushBrowserLogs();
        // }
    }

    protected function currentModel(Model $model): void
    {
        $this->currentModel = $model;
    }
}