<?php

declare(strict_types=1);

/**
 * Comprehensive examples of the improved Container usage.
 */
class ContainerUsageExamples
{
    private Container $container;

    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    /**
     * Example 1: Basic binding and resolution.
     */
    public function basicUsage(): void
    {
        // Simple binding
        $this->container->bind('config.database.host', 'localhost');
        $this->container->bind('config.database.port', 3306);

        // Class binding
        $this->container->bind(UserRepository::class, DatabaseUserRepository::class);

        // Singleton binding
        $this->container->singleton(Logger::class, FileLogger::class);

        // Resolve instances
        $host = $this->container->get('config.database.host'); // 'localhost'
        $userRepo = $this->container->get(UserRepository::class); // DatabaseUserRepository instance
        $logger = $this->container->get(Logger::class); // Same FileLogger instance every time
    }

    /**
     * Example 2: Advanced parameter resolution.
     */
    public function parameterResolution(): void
    {
        // Bind with specific parameters
        $this->container->bind(DatabaseConnection::class, function ($container) {
            return new DatabaseConnection(
                host: $container->get('config.database.host'),
                port: $container->get('config.database.port'),
                username: $container->get('config.database.username'),
                password: $container->get('config.database.password')
            );
        });

        // Set global parameters
        $this->container->setGlobalParameters([
            'app_name' => 'My Application',
            'debug' => true,
            'cache_ttl' => 3600,
        ]);

        // Bind parameters for specific class
        $this->container->bindParams(EmailService::class, [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'from_email' => 'noreply@myapp.com',
        ]);
    }

    /**
     * Example 3: Factory and closure bindings.
     */
    public function factoryBindings(): void
    {
        // Factory binding
        $this->container->factory(CacheManager::class, function ($container) {
            $driver = $container->get('config.cache.driver');

            return match ($driver) {
                'redis' => new RedisCache($container->get(RedisConnection::class)),
                'file' => new FileCache($container->get('config.cache.path')),
                'memory' => new MemoryCache(),
                default => throw new InvalidArgumentException("Unknown cache driver: {$driver}")
            };
        });

        // Closure binding with parameters
        $this->container->bind('mailer', function ($container, $parameters) {
            $config = $parameters['config'] ?? $container->get('config.mail');
            return new Mailer($config);
        });
    }

    /**
     * Example 4: Tagged services.
     */
    public function taggedServices(): void
    {
        // Bind services with tags
        $this->container->bindWithTags(EmailNotificationHandler::class, null, ['notification_handler']);
        $this->container->bindWithTags(SmsNotificationHandler::class, null, ['notification_handler']);
        $this->container->bindWithTags(PushNotificationHandler::class, null, ['notification_handler']);

        // Or tag existing bindings
        $this->container->bind(SlackNotificationHandler::class);
        $this->container->tag(SlackNotificationHandler::class, 'notification_handler');

        // Resolve all tagged services
        $handlers = $this->container->tagged('notification_handler');

        // Use in a notification service
        $this->container->bind(NotificationService::class, function ($container) {
            return new NotificationService($container->tagged('notification_handler'));
        });
    }

    /**
     * Example 5: Aliases and contextual binding.
     */
    public function aliasesAndContextual(): void
    {
        // Create aliases
        $this->container->alias(UserRepository::class, 'user.repository');
        $this->container->alias(Logger::class, 'logger');

        // Use aliases
        $userRepo = $this->container->get('user.repository');
        $logger = $this->container->get('logger');

        // Contextual binding (different implementations based on context)
        $this->container->bind('cache.user', RedisCache::class);
        $this->container->bind('cache.session', FileCache::class);
    }

    /**
     * Example 6: Method injection and callable resolution.
     */
    public function methodInjection(): void
    {
        // Call method with dependency injection
        $result = $this->container->call([UserController::class, 'index']);

        // Call closure with dependency injection
        $result = $this->container->call(function (UserService $userService, Logger $logger) {
            $logger->info('Processing users');
            return $userService->getAllUsers();
        });

        // Call string callback
        $result = $this->container->call('UserController@show', ['id' => 123]);
    }

    /**
     * Example 7: Complex dependency resolution.
     */
    public function complexDependencies(): void
    {
        // Class with multiple dependency types
        class ComplexService
        {
            public function __construct(
                private UserRepository $userRepo,      // Interface injection
                private string $apiKey,                // String parameter
                private int $timeout = 30,             // Default value
                private ?Logger $logger = null,        // Nullable dependency
                private array $config = []             // Array parameter
            ) {
            }
        }

        // Bind the complex service
        $this->container->bindParams(ComplexService::class, [
            'apiKey' => 'secret-api-key-123',
            'timeout' => 60,
            'config' => ['retries' => 3, 'delay' => 1000],
        ]);

        // Container will automatically resolve all dependencies
        $service = $this->container->get(ComplexService::class);
    }

    /**
     * Example 8: Union and intersection types (PHP 8+).
     */
    public function modernPhpTypes(): void
    {
        // Class with union types
        class ModernService
        {
            public function __construct(
                private Logger|NullLogger $logger,           // Union type
                private string|int $identifier,              // Built-in union
                private UserRepository&Cacheable $repo       // Intersection type
            ) {
            }
        }

        // Container handles these automatically
        $service = $this->container->get(ModernService::class);
    }

    /**
     * Example 9: Rebound callbacks and lifecycle management.
     */
    public function lifecycleManagement(): void
    {
        // Register rebound callback
        $this->container->rebinding(Logger::class, function ($container, $logger) {
            // Configure logger when it's rebound
            $logger->setLevel($container->get('config.log.level'));
        });

        // Instance management
        $this->container->instance('current_user', new User(['id' => 1, 'name' => 'John']));

        // Remove bindings
        $this->container->remove(Logger::class);

        // Check if bound
        if ($this->container->has(UserService::class)) {
            $service = $this->container->get(UserService::class);
        }
    }

    /**
     * Example 10: Performance and debugging.
     */
    public function performanceAndDebugging(): void
    {
        // Disable auto-wiring for performance
        $this->container->setAutoWiring(false);

        // Explicit bindings for better performance
        $this->container->bind(UserService::class, function ($container) {
            return new UserService(
                $container->get(UserRepository::class),
                $container->get(Logger::class)
            );
        });

        // Re-enable auto-wiring
        $this->container->setAutoWiring(true);

        // Flush container (useful for testing)
        $this->container->flush();
    }

    /**
     * Example 11: Real-world application setup.
     */
    public function realWorldSetup(): void
    {
        // Configuration
        $this->container->setGlobalParameters([
            'app.name' => 'E-Commerce Platform',
            'app.version' => '1.0.0',
            'app.debug' => false,
        ]);

        // Core services
        $this->container->singleton(Database::class, function ($container) {
            return new Database(
                host: $_ENV['DB_HOST'] ?? 'localhost',
                username: $_ENV['DB_USER'] ?? 'root',
                password: $_ENV['DB_PASS'] ?? '',
                database: $_ENV['DB_NAME'] ?? 'app'
            );
        });

        $this->container->singleton(Logger::class, FileLogger::class);
        $this->container->singleton(Cache::class, RedisCache::class);

        // Repositories
        $this->container->bind(UserRepository::class, DatabaseUserRepository::class);
        $this->container->bind(ProductRepository::class, DatabaseProductRepository::class);
        $this->container->bind(OrderRepository::class, DatabaseOrderRepository::class);

        // Services
        $this->container->bind(UserService::class);
        $this->container->bind(ProductService::class);
        $this->container->bind(OrderService::class);
        $this->container->bind(PaymentService::class);

        // Tag notification handlers
        $this->container->bindWithTags(EmailNotificationHandler::class, null, ['notification']);
        $this->container->bindWithTags(SmsNotificationHandler::class, null, ['notification']);

        // Controllers (auto-wired)
        $this->container->bind(UserController::class);
        $this->container->bind(ProductController::class);
        $this->container->bind(OrderController::class);

        // Aliases for convenience
        $this->container->alias(UserRepository::class, 'users');
        $this->container->alias(ProductRepository::class, 'products');
        $this->container->alias(Logger::class, 'log');
    }
}