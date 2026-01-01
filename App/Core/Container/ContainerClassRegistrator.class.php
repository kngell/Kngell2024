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
            ApiClientInterface::class => CurlApiGateway::class,
            MailerInterface::class => Mailer::class,
            EventManagerInterface::class => EventManager::class,
            MenuItemInterface::class => MenuItem::class,
            EntityManagerInterface::class => EntityManager::class,
            RooterInterface::class => Rooter::class,
            CookieServiceInterface::class => CookieService::class,
            'currency.cache' => function (): CacheInterface {
                return CacheFactory::createCurrencyCache();
            },
            'region.cache' => function (): CacheInterface {
                return CacheFactory::createRegionCache();
            },
            'locale.cache' => function (): CacheInterface {
                return CacheFactory::createLocaleCache();
            },
            SmartSerializerInterface::class => SmartSerializer::class,
            CurrencyCodeProviderInterface::class => CurrencyCodeProvider::class,
            CurrencyResolverInterface::class => CurrencyResolver::class,
            LocaleProviderInterface::class => function (App $app): DatabaseLocaleProvider {
                return new DatabaseLocaleProvider(
                    localeModel: $app->get(RegionLocaleModel::class),
                    regionLocaleModel: $app->get(RegionLocaleMappingModel::class),
                    regionModel: $app->get(RegionModel::class),
                    defaultLocale:$app->getAppConfig()->getConfig()['default_locale'],
                    builtinLocaleData:$app->getAppConfig()->getConfig()['builtin_locale_data'],
                    cache: $app->get('locale.cache'),
                );
            },
            RegionDataProviderInterface::class => RegionDataProvider::class,
            DatabaseEnvironmentConfig::class => [
                function () {
                    return YamlFile::get('database');
                }, 'mysql'],
            ListenerProviderInterface::class => [ListenerProvider::class, YamlFile::get('eventListener')],
            MailerFacade::class => function () {
                return YamlFile::get('email_settings');
            },
            ProductFormCreator::class => ProductFormCreator::class,
            FileSearchInterface::class => FileSearchManager::class,
            FileUploadComponentInterface::class => UploadService::class,
            FileContentInterface::class => FileContentManager::class,
            DirectoryInterface::class => DirectoryManager::class,
            FileOperationsInterface::class => FileOperationsManager::class,

            VariationBuilderInterface::class => DatabaseVariationBuilder::class,
            CurrencyLookupInterface::class => CurrencyService::class,
            RegionContextInterface::class => RegionContext::class,
            LoggerInterface::class => CustomLogger::class,

            SessionInterface::class => Session::class,
            CacheInterface::class => Cache::class,
            SqlCompositeQueryBuilderInterface::class => QueryBuilder::class,
            // Entity
            EntityMapperInterface::class => EntityMapper::class,
            EntityRelationManagerInterface::class => EntityRelationManager::class,
            EntityHydratorInterface::class => EntityHydrator::class,
            EntityFactoryInterface::class => EntityFactory::class,
            EntityCachingServiceInterface::class => EntityCachingService::class,
            EntityDataSerializerInterface::class => EntityCacheDataSerializer::class,
            EntityCacheKeyGeneratorInterface::class => EntityCacheKeyGenerator::class,

            //Model
            ModelUtilityInterface::class => ModelUtility::class,
            ModelFactoryInterface::class => DefaultModelFactory::class,
            ModelContextInterface::class => ModelContext::class,
        ];
    }

    private static function singletonClasses(App $app): array
    {
        return [
            SuperGlobalsInterface::class => SuperGlobals::class,
            Request::class => Request::class,
            ValidatorInterface::class => Validator::class,
            DatabaseConnectionInterface::class => PDOConnection::class,
            UserModel::class => UserModel::class,
            FlashInterface::class => Flash::class,
            ViewInterface::class => View::class,
            CollectionInterface::class => Collection::class,
            SessionEnvironment::class => SessionEnvironment::class,
            SessionStorageInterface::class => NativeSessionStorage::class,
            CacheStorageInterface::class => NativeCacheStorage::class,
            CookieStoreInterface::class => NativeCookieStore::class,
            CookieInterface::class => Cookie::class,
            DataMapperInterface::class => DataMapper::class,
            HashInterface::class => [Hash::class, function () use ($app) {
                return $app->getAppConfig()->getConfig()['security'];
            }],
            RouteCollector::class => RouteCollector::class,
            RouteMatcher::class => RouteMatcher::class,
            RouteArgumentGenerator::class => RouteArgumentGenerator::class,
            RouteResponseGenerator::class => RouteResponseGenerator::class,

            // Form-related singletons
            HtmlBuilder::class => HtmlBuilder::class,
            FieldRenderer::class => FieldRenderer::class,
            FieldGroupRenderer::class => FieldGroupRenderer::class,
            SectionRenderer::class => SectionRenderer::class,
            ButtonBuilder::class => ButtonBuilder::class,
            IconBuilder::class => IconBuilder::class,
            FieldIdGenerator::class => FieldIdGenerator::class,
            TokenInterface::class => Token::class,
            NavigationHistoryService::class => NavigationHistoryService::class,
            RememberPreviousPageMiddleware::class => RememberPreviousPageMiddleware::class,
            TypeNormalizerInterface::class => DefaultTypeNormalizer::class,
            ChangeTrackerInterface::class => ChangeTracker::class,
            EntityDependenciesFactoryInterface::class => function () use ($app) {
                return new EntityDependenciesFactory(
                    $app->get(TypeNormalizerInterface::class),
                    null,
                    function () use ($app) {
                        // This will be called when TypePresenterFactory is first needed
                        return new TypePresenterFactory(
                            $app->get(TranslatorServiceInterface::class),
                            $app->get(RegionContextInterface::class),
                        );
                    },
                );
            },
            //Forms
            FormDataHandlerInterface::class => FormDataHandlerService::class,

            //Region Context
            FormatterInterface::class => Formatter::class,
            FallbackSymbolProviderInterface::class => DefaultFallbackSymbolProvider::class,

            //Translation
            TranslatorServiceInterface::class => TranslatorService::class,
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

        // Form factories - THIS IS THE KEY PART!
        $formFactories = [
            MainProductFormFactory::class,
            DeleteProductFormFactory::class,
            BulkProductFormFactory::class,
        ];

        foreach ($formFactories as $factory) {
            // Tag each factory with both specific and interface tags
            $app->tag($factory, 'form_factories');
            $app->tag($factory, FormFactoryInterface::class);
        }

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

        // Other form services
        $formServices = [
            ProductFormCreator::class,
            FormSectionManager::class,
            FormProgressCalculator::class,
            VariationBuilderInterface::class,
        ];
        foreach ($formServices as $service) {
            $app->tag($service, 'forms');
        }
    }

    /**
     * Register aliases for commonly used services.
     */
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
            EventManagerInterface::class => 'events',

            // Form aliases
            ProductFormCreator::class => 'form.creator',
        ];

        foreach ($aliases as $abstract => $alias) {
            $app->alias($abstract, $alias);
        }
    }

    /**
     * Register global parameters available throughout the application.
     */
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