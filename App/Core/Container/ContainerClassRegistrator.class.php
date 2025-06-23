<?php

declare(strict_types=1);

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

    private static function params(array|Closure $concrete, string $abstract)
    {
        if ($concrete instanceof Closure) {
            return [$abstract, $concrete];
        }
        if (count($concrete) === 1) {
            return [$abstract, $concrete[0]];
        }
        $class = $concrete[0];
        if (! is_string($class)) {
            $class = $abstract;
        } else {
            unset($concrete[0]);
            $concrete = array_values($concrete);
        }
        return [$class, $concrete];
    }

    private static function bindClasses() : array
    {
        return [
            ApiClientInterface::class => CurlApiGateway::class,
            MailerInterface::class => Mailer::class,
            EventManagerInterface::class => EventManager::class,
            MenuItemInterface::class => MenuItem::class,
            EntityManagerInterface::class => EntityManager::class,
            AbstractFactory::class => ConcreteFactory1::class,
            RooterInterface::class => Rooter::class,
            FilesSystemInterface::class => FileSystem::class,
            RouteDispatcher::class => function (App $app) {
                $routeArgumentGenerator = $app->get(RouteArgumentGenerator::class);
                $middlewares = YamlFile::get('middlewares');
                return new RouteDispatcher($routeArgumentGenerator, $middlewares);
            },
            DatabaseEnvironmentConfig::class => [function () {
                return YamlFile::get('database');
            }, 'mysql'],
            ListenerProviderInterface::class => [ListenerProvider::class, YamlFile::get('eventListener')],
            MailerFacade::class => function () {
                return YamlFile::get('email_settings');
            },
        ];
    }

    private static function singletonClasses(App $app) : array
    {
        return [
            SuperGlobalsInterface::class => SuperGlobals::class,
            Request::class => Request::class,
            ValidatorInterface::class => Validator::class,
            DatabaseConnectionInterface::class => PDOConnection::class,
            UserModel::class => UserModel::class,
            FlashInterface::class => Flash::class,
            TokenInterface::class => Token::class,
            ViewInterface::class => View::class,
            CollectionInterface::class => Collection::class,
            SessionEnvironment::class => SessionEnvironment::class,
            SessionStorageInterface::class => NativeSessionStorage::class,
            SessionInterface::class => Session::class,
            CacheStorageInterface::class => NativeCacheStorage::class,
            CookieStoreInterface::class => NativeCookieStore::class,
            CookieInterface::class => Cookie::class,
            DataMapperInterface::class => DataMapper::class,
            HashInterface::class => [Hash::class, function () use ($app) {
                return $app->getAppConfig()->getConfig()['security'];
            }],
            FileUploadInterface::class => ImagesUpload::class,
            RouteMatcher::class => RouteMatcher::class,
            RouteArgumentGenerator::class => RouteArgumentGenerator::class,
            RouteResponseGenerator::class => RouteResponseGenerator::class,
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
            FileUploadInterface::class,
            FilesSystemInterface::class,
        ];
        foreach ($infrastructureServices as $service) {
            $app->tag($service, 'infrastructure');
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
            FileUploadInterface::class => 'uploader',
            EventManagerInterface::class => 'events',
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
        ]);
    }
}