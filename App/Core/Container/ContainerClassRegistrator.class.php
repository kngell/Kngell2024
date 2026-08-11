<?php

declare(strict_types=1);

use Dom\Entity;
use Psr\Log\LoggerInterface;

final readonly class ContainerClassRegistrator
{
    private function __construct()
    {
    }

    public static function register(App &$app): void
    {
        // Register regular bindings
        foreach (self::bindClasses() as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'bind', $app);
        }

        // Register singleton bindings
        foreach (self::singletonClasses($app) as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'singleton', $app);
        }

        // Register tagged services for better organization
        self::registerTaggedServices($app);

        // Set up aliases for commonly used services
        self::registerAliases($app);

        // Set up global parameters
        self::registerGlobalParameters($app);
    }

    private static function registerClass(string $abstract, mixed $concrete, string $function, App $app): void
    {
        if (is_array($concrete) || $concrete instanceof Closure) {
            list($class, $args) = self::params($concrete, $abstract);

            if ($concrete instanceof Closure) {
                // Use factory binding for closures
                $app->factory($abstract, $concrete);
            } else {
                // Use parameter binding for arrays
                $app->$function($abstract, $class, $function === 'singleton', $args);
            }
        } else {
            $app->$function($abstract, $concrete);
        }
    }

    private static function params(array|Closure $concrete, string $abstract): array
    {
        if ($concrete instanceof Closure) {
            return [$abstract, $concrete];
        }

        if (count($concrete) === 1) {
            return [$abstract, $concrete[0]];
        }

        $class = $concrete[0];
        if (!is_string($class)) {
            $class = $abstract;
        } else {
            unset($concrete[0]);
            $concrete = array_values($concrete);
        }
        return [$class, $concrete];
    }

    private static function bindClasses(): array
    {
        return [
            // ========================================
            // API & HTTP CLIENTS
            // ========================================
            ApiClientInterface::class => CurlApiGateway::class,

            // ========================================
            // EVENT SYSTEM
            // ========================================
            EventDispatcherInterface::class => EventDispatcher::class,

            // ========================================
            // MENU & NAVIGATION
            // ========================================
            MenuItemInterface::class => MenuItem::class,

            // ========================================
            // COOKIE SERVICES
            // ========================================
            CookieServiceInterface::class => CookieService::class,

            // ========================================
            // CONFIGURATION LOADERS (request-scoped)
            // ========================================
            DatabaseEnvironmentConfig::class => [
                function () {
                    return YamlFile::get('database');
                }, 'mysql',
            ],
            ListenerProviderInterface::class => [
                ListenerProvider::class,
                YamlFile::get('eventListener'),
            ],
            MailerFacade::class => function () {
                return YamlFile::get('email_settings');
            },

            // ========================================
            // FILE SYSTEM OPERATIONS
            // ========================================
            FileSearchInterface::class => FileSearchManager::class,
            FileUploadComponentInterface::class => UploadService::class,
            FileContentInterface::class => FileContentManager::class,
            DirectoryInterface::class => DirectoryManager::class,
            FileOperationsInterface::class => FileOperationsManager::class,
            ChangeTrackerInterface::class => ChangeTracker::class,

            // ========================================
            // CURRENCY & QUERY BUILDERS
            // ========================================
            CurrencyLookupInterface::class => CurrencyService::class,
            SqlCompositeQueryBuilderInterface::class => QueryBuilder::class,

            // ========================================
            // ENTITY RELATIONS & CACHING (request-scoped)
            // ========================================
            EntityRelationManagerInterface::class => EntityRelationManager::class,
            EntityCachingServiceInterface::class => EntityCachingService::class,

            // ========================================
            // MODEL UTILITIES
            // ========================================
            ModelUtilityInterface::class => ModelUtility::class,
            ModelFactoryInterface::class => DefaultModelFactory::class,
            ModelContextInterface::class => ModelContext::class,
            UserModel::class => UserModel::class,
        ];
    }

    private static function singletonClasses(App $app): array
    {
        return [
            // ========================================
            // 1. CACHE SYSTEMS
            // ========================================
            'currency.cache' => function (): CacheInterface {
                return CacheFactory::createCurrencyCache();
            },
            'region.cache' => function (): CacheInterface {
                return CacheFactory::createRegionCache();
            },
            'locale.cache' => function (): CacheInterface {
                return CacheFactory::createLocaleCache();
            },
            CacheInterface::class => Cache::class,
            CacheStorageInterface::class => NativeCacheStorage::class,

            // ========================================
            // 2. SESSION & COOKIE
            // ========================================
            SessionEnvironment::class => SessionEnvironment::class,
            SessionStorageInterface::class => NativeSessionStorage::class,
            SessionInterface::class => Session::class,
            CookieStoreInterface::class => NativeCookieStore::class,
            CookieInterface::class => Cookie::class,

            // ========================================
            // 3. HTTP & REQUEST HANDLING
            // ========================================
            SuperGlobalsInterface::class => SuperGlobals::class,
            Request::class => Request::class,
            RouteCollector::class => RouteCollector::class,
            RouteMatcher::class => RouteMatcher::class,
            RouteArgumentGenerator::class => RouteArgumentGenerator::class,
            RouteResponseGenerator::class => RouteResponseGenerator::class,
            RooterInterface::class => Rooter::class,

            // ========================================
            // 4. DATABASE & ENTITY MANAGEMENT
            // ========================================
            DatabaseConnectionInterface::class => PDOConnection::class,
            EntityManagerInterface::class => EntityManager::class,
            EntityMapperInterface::class => EntityMapper::class,
            EntityHydratorInterface::class => EntityHydrator::class,
            EntityFactoryInterface::class => EntityFactory::class,
            EntityDataSerializerInterface::class => EntityCacheDataSerializer::class,
            EntityCacheKeyGeneratorInterface::class => EntityCacheKeyGenerator::class,
            EntityDependenciesFactoryInterface::class => function () use ($app) {
                return new EntityDependenciesFactory(
                    $app->get(TypeNormalizerInterface::class),
                    null,
                    function () use ($app) {
                        return new TypePresenterFactory(
                            $app->get(TranslatorServiceInterface::class),
                            $app->get(ObfuscatorManager::class),
                            $app->get(RegionContextInterface::class),
                            $app->get(MoneyPresenter::class),
                        );
                    },
                    $app->get(EntityIdentityMap::class),
                );
            },

            // ========================================
            // 5. DATA PROVIDERS & RESOLVERS
            // ========================================
            RegionDataProviderInterface::class => RegionDataProvider::class,
            CurrencyResolverInterface::class => CurrencyResolver::class,
            CurrencyCodeProviderInterface::class => CurrencyCodeProvider::class,
            LocaleProviderInterface::class => function (App $app): DatabaseLocaleProvider {
                return new DatabaseLocaleProvider(
                    localeModel: $app->get(RegionLocaleModel::class),
                    regionLocaleModel: $app->get(RegionLocaleMappingModel::class),
                    regionModel: $app->get(RegionModel::class),
                    defaultLocale: $app->getAppConfig()->getConfig()['default_locale'],
                    builtinLocaleData: $app->getAppConfig()->getConfig()['builtin_locale_data'],
                    cache: $app->get('locale.cache'),
                );
            },

            // ========================================
            // 6. REGION CONTEXT
            // ========================================
            RegionContextInterface::class => RegionContext::class,

            // ========================================
            // 7. VALIDATION & SECURITY
            // ========================================
            ValidatorInterface::class => Validator::class,
            HashInterface::class => [Hash::class, function () use ($app) {
                return $app->getAppConfig()->getConfig()['security'];
            }],
            TokenInterface::class => Token::class,
            ListenerResolverInterface::class => ListenerResolver::class,

            // ========================================
            // 8. DATA MAPPING & MODELS
            // ========================================
            DataMapperInterface::class => DataMapper::class,

            // ========================================
            // 9. SERIALIZATION
            // ========================================
            SmartSerializerInterface::class => SmartSerializer::class,
            TypeNormalizerInterface::class => DefaultTypeNormalizer::class,

            // ========================================
            // 10. VIEW & PRESENTATION
            // ========================================
            ViewInterface::class => View::class,
            FlashInterface::class => Flash::class,
            CollectionInterface::class => Collection::class,

            // ========================================
            // 11. HTML BUILDERS & FORM RENDERERS
            // ========================================
            HtmlBuilder::class => HtmlBuilder::class,
            FieldRenderer::class => FieldRenderer::class,
            FieldGroupRenderer::class => FieldGroupRenderer::class,
            SectionRenderer::class => SectionRenderer::class,
            ButtonBuilder::class => ButtonBuilder::class,
            IconBuilder::class => IconBuilder::class,
            FieldIdGenerator::class => FieldIdGenerator::class,
            FormDataHandlerInterface::class => FormDataHandlerService::class,

            // ========================================
            // 12. HTML SECTIONS & TEMPLATES
            // ========================================
            HtmlSectionManagerInterface::class => HtmlRegularSectionManager::class,
            HtmlTemplatePathInterface::class => HtmlTemplatePathManager::class,

            // ========================================
            // 13. FORMATTERS & PRESENTERS
            // ========================================
            FormatterInterface::class => Formatter::class,
            FallbackSymbolProviderInterface::class => DefaultFallbackSymbolProvider::class,
            MoneyType::class => function () use ($app) {
                return new MoneyType(
                    new LazyCurrencyCodeProvider(
                        fn () => $app->get(CurrencyCodeProviderInterface::class),
                    ),
                );
            },
            PriceRangeType::class => function () use ($app) {
                return new PriceRangeType(
                    new LazyCurrencyCodeProvider(
                        fn () => $app->get(CurrencyCodeProviderInterface::class),
                    ),
                );
            },

            // ========================================
            // 14. TRANSLATION
            // ========================================
            TranslatorServiceInterface::class => TranslatorService::class,

            // ========================================
            // 15. NAVIGATION & MIDDLEWARE
            // ========================================
            NavigationHistoryService::class => NavigationHistoryService::class,
            RememberPreviousPageMiddleware::class => RememberPreviousPageMiddleware::class,

            // ========================================
            // 16. EMAIL & FILE OPERATIONS
            // ========================================
            MailerInterface::class => Mailer::class,
            FileMoverInterface::class => FileMoverService::class,

            // ========================================
            // 17. LOGGING
            // ========================================
            LoggerInterface::class => CustomLogger::class,
            // ========================================
            // 18. AUTHENTICATION
            // ========================================
            AuthService::class => AuthService::class,
        ];
    }

    /**
     * Register tagged services for better organization.
     */
    private static function registerTaggedServices(App $app): void
    {
        // Core services
        $coreServices = [
            Request::class,
            Response::class,
            SessionInterface::class,
            CacheInterface::class,
            CookieInterface::class,
        ];
        foreach ($coreServices as $service) {
            $app->tag($service, 'core');
        }

        // Data layer services
        $dataServices = [
            DatabaseConnectionInterface::class,
            DataMapperInterface::class,
            UserModel::class,
        ];
        foreach ($dataServices as $service) {
            $app->tag($service, 'data');
        }

        // Security services
        $securityServices = [
            HashInterface::class,
            TokenInterface::class,
            ValidatorInterface::class,
        ];
        foreach ($securityServices as $service) {
            $app->tag($service, 'security');
        }

        // View and presentation services
        $presentationServices = [
            ViewInterface::class,
            FlashInterface::class,
        ];
        foreach ($presentationServices as $service) {
            $app->tag($service, 'presentation');
        }

        // Infrastructure services
        $infrastructureServices = [
            MailerInterface::class,
        ];
        foreach ($infrastructureServices as $service) {
            $app->tag($service, 'infrastructure');
        }

        // Region context resolvers
        $regionContext = [
            AcceptLanguageRegionContext::class,
            GeoIPRegionContext::class,
            HeaderRegionContext::class,
            QueryParameterRegionContext::class,
            SessionRegionContext::class,
            CookieRegionContext::class,
        ];
        foreach ($regionContext as $context) {
            $app->tag($context, 'contexts');
            $app->tag($context, RegionContextResolutionInterface::class);
        }

        // Form services
        $formServices = [
            FormCreatorService::class,
            HtmlRegularSectionManager::class,
            FormProgressCalculator::class,
        ];
        foreach ($formServices as $service) {
            $app->tag($service, 'forms');
        }
    }

    private static function registerAliases(App $app): void
    {
        $aliases = [
            // Core aliases
            Request::class => 'request',
            Response::class => 'response',
            SessionInterface::class => 'session',
            CacheInterface::class => 'cache',
            CookieInterface::class => 'cookie',

            // Data aliases
            DatabaseConnectionInterface::class => 'db',
            UserModel::class => 'users',
            DataMapperInterface::class => 'mapper',

            // Security aliases
            HashInterface::class => 'hash',
            TokenInterface::class => 'token',
            ValidatorInterface::class => 'validator',

            // View aliases
            ViewInterface::class => 'view',
            FlashInterface::class => 'flash',

            // Infrastructure aliases
            MailerInterface::class => 'mailer',
            EventDispatcherInterface::class => 'events',

            // Form aliases
            FormCreatorService::class => 'form.creator',
        ];

        foreach ($aliases as $abstract => $alias) {
            $app->alias($abstract, $alias);
        }
    }

    private static function registerGlobalParameters(App $app): void
    {
        $app->setGlobalParameters([
            'container.version' => '2.0.0',
            'container.features' => [
                'auto_wiring',
                'tagged_services',
                'method_injection',
                'circular_dependency_detection',
                'contextual_binding',
            ],
            'framework.name' => 'K\'nGELL',
            'globalMiddlewares' => YamlFile::get('middlewares'),
        ]);
    }
}