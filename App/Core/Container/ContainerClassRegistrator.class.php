<?php

declare(strict_types=1);

final readonly class ContainerClassRegistrator
{
    private function __construct()
    {
    }

    public static function register(App &$app) : void
    {
        foreach (self::bindClasses() as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'bind', $app);
        }
        foreach (self::singletonClasses($app) as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'singleton', $app);
        }
    }

    private static function registerClass(string $abstract, mixed $concrete, string $function, App $app) : void
    {
        if (is_array($concrete) || $concrete instanceof Closure) {
            list($class, $args) = self::params($concrete, $abstract);
            $app->$function($abstract, $class, false, $args);
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
            MailerInterface::class => Mailer::class,
            EventManagerInterface::class => EventManager::class,
            MenuItemInterface::class => MenuItem::class,
            EntityManagerInterface::class => EntityManager::class,
            AbstractFactory::class => ConcreteFactory1::class,
            RooterInterface::class => Rooter::class,
            CacheStorageInterface::class => NativeCacheStorage::class,
            CacheInterface::class => Cache::class,
            FilesSystemInterface::class => FileSystem::class,
            SuperGlobalsInterface::class => SuperGlobals::class,
            CookieStoreInterface::class => NativeCookieStore::class,
            CookieInterface::class => Cookie::class,
            RouteDispatcher::class => [false,
                function () {
                    return YamlFile::get('middlewares');
                }],
            DatabaseEnvironmentConfig::class => [function () {
                return YamlFile::get('database');
            }, 'mysql',
            ],
            ListenerProviderInterface::class => [ListenerProvider::class, YamlFile::get('eventListener')],
            MailerFacade::class => function () {
                return YamlFile::get('email_settings');
            },

        ];
    }

    private static function singletonClasses(App $app) : array
    {
        return [
            ValidatorInterface::class => Validator::class,
            DatabaseConnexionInterface::class => PDOConnexion::class,
            UserModel::class => UserModel::class,
            FlashInterface::class => Flash::class,
            TokenInterface::class => Token::class,
            ViewInterface::class => View::class,
            CollectionInterface::class => Collection::class,
            SessionEnvironment::class => SessionEnvironment::class,
            SessionStorageInterface::class => NativeSessionStorage::class,
            SessionInterface::class => Session::class,
            DataMapperInterface::class => DataMapper::class,
            HashInterface::class => [Hash::class, function () use ($app) {
                return $app->getAppConfig()->getConfig()['security'];
            }],
        ];
    }
}