# App Container Usage Examples

## Overview

Your App class now extends the improved Container, providing powerful dependency injection capabilities throughout your application. Here are practical examples of how to use these features.

## Basic Usage Patterns

### 1. **Static Access from Anywhere**

```php
// In any class, anywhere in your application
class SomeService
{
    public function doSomething(): void
    {
        // Get services using static methods
        $users = App::diGet(UserModel::class);
        $logger = App::diGet('logger'); // Using alias

        // Check if service exists
        if (App::diHas(CacheInterface::class)) {
            $cache = App::diGet('cache');
        }

        // Call methods with dependency injection
        $result = App::diCall(function(UserService $userService, Logger $logger) {
            return $userService->getAllUsers();
        });
    }
}
```

### 2. **Controller Usage**

```php
class UserController extends Controller
{
    // Dependencies automatically injected via constructor
    public function __construct(
        private UserService $userService,
        private Logger $logger,
        private CacheInterface $cache
    ) {}

    public function index(): string
    {
        // Use injected dependencies
        $users = $this->userService->getAllUsers();

        // Or get additional services on demand
        $validator = App::diGet('validator');

        // Use method injection for complex operations
        $result = App::diCall([$this, 'processUsers'], [
            'users' => $users,
            'limit' => 10
        ]);

        return $this->render('users/index', ['users' => $result]);
    }

    // Method with dependency injection
    private function processUsers(
        array $users,
        int $limit,
        UserProcessor $processor, // Automatically injected
        CacheInterface $cache     // Automatically injected
    ): array {
        return $processor->process($users, $limit, $cache);
    }
}
```

### 3. **Service Classes**

```php
class UserService
{
    // Constructor injection works automatically
    public function __construct(
        private UserRepository $repository,
        private Logger $logger,
        private EventManagerInterface $events,
        private string $app_name,  // From global parameters
        private bool $debug = false // From global parameters
    ) {}

    public function createUser(array $data): User
    {
        // Use method injection for complex operations
        return App::diCall(function(
            ValidatorInterface $validator,
            HashInterface $hash,
            TokenInterface $token
        ) use ($data) {
            // Validate data
            $errors = $validator->validate($data, 'user_registration');
            if (!empty($errors)) {
                throw new ValidationException($errors);
            }

            // Hash password
            $data['password'] = $hash->password($data['password']);

            // Generate verification token
            $data['verification_token'] = $token->generate();

            return $this->repository->create($data);
        });
    }
}
```

## Advanced Usage Patterns

### 4. **Tagged Services**

```php
class NotificationService
{
    public function __construct()
    {
        // Get all notification handlers
        $this->handlers = App::diGet('app')->tagged('notification');
    }

    public function send(string $message): void
    {
        foreach ($this->handlers as $handler) {
            $handler->send($message);
        }
    }
}

// Register notification handlers
App::diBind(EmailNotificationHandler::class);
App::diBind(SmsNotificationHandler::class);

$app = App::getInstance();
$app->tag([
    EmailNotificationHandler::class,
    SmsNotificationHandler::class
], 'notification');
```

### 5. **Factory Patterns**

```php
// In a service provider or bootstrap file
$app = App::getInstance();

$app->factory(PaymentGatewayInterface::class, function($container) {
    $config = $container->resolve('config.payment');

    return match($config['default_gateway']) {
        'paypal' => $container->resolve(PaypalGateway::class),
        'stripe' => $container->resolve(StripeGateway::class),
        'square' => $container->resolve(SquareGateway::class),
        default => throw new InvalidArgumentException('Unknown payment gateway')
    };
});

// Usage anywhere in the application
class PaymentController extends Controller
{
    public function processPayment(): Response
    {
        // Gets the correct gateway based on configuration
        $gateway = App::diGet(PaymentGatewayInterface::class);

        return $gateway->processPayment($this->request->getPost()->getAll());
    }
}
```

### 6. **Contextual Binding**

```php
// Different cache implementations for different contexts
$app = App::getInstance();

// User cache uses Redis
$app->bind('cache.users', function($container) {
    return new RedisCache($container->resolve(RedisConnection::class));
});

// Session cache uses files
$app->bind('cache.sessions', function($container) {
    return new FileCache('/tmp/sessions');
});

// Usage
class UserService
{
    public function __construct(
        private CacheInterface $cache // Gets 'cache.users'
    ) {}
}

class SessionService
{
    public function __construct(
        private CacheInterface $cache // Gets 'cache.sessions'
    ) {}
}
```

### 7. **Global Parameters Usage**

```php
// Set global parameters (usually in bootstrap)
$app = App::getInstance();
$app->setGlobalParameters([
    'api_key' => $_ENV['API_KEY'],
    'api_timeout' => 30,
    'debug_mode' => $_ENV['DEBUG'] === 'true',
    'upload_path' => '/uploads',
    'max_file_size' => 5 * 1024 * 1024, // 5MB
]);

// Use in any service
class ApiService
{
    public function __construct(
        private string $api_key,      // Automatically injected
        private int $api_timeout,     // Automatically injected
        private bool $debug_mode      // Automatically injected
    ) {}
}

class FileUploadService
{
    public function __construct(
        private string $upload_path,    // Automatically injected
        private int $max_file_size      // Automatically injected
    ) {}
}
```

## RouteDispatcher Integration

### 8. **Enhanced Route Handling**

```php
// The RouteDispatcher now provides better dependency injection
class RouteDispatcher
{
    public function dispatch(RouteInfo $route, string $url, App $app, array $params, Request $request): Response
    {
        // Route information is now available for injection
        $app->instance('current.route', $route);
        $app->instance('current.url', $url);

        // Controllers get automatic dependency injection
        $controller = $app->resolve($route->getController());

        // Method injection for route methods
        return $app->call([$controller, $route->getMethod()->getName()], $params);
    }
}

// Controllers can now access route information
class ProductController extends Controller
{
    public function show(
        int $id,                    // From route parameters
        RouteInfo $currentRoute,    // Injected automatically
        string $currentUrl,         // Injected automatically
        ProductService $products    // Service injection
    ): string {
        $product = $products->findById($id);

        return $this->render('products/show', [
            'product' => $product,
            'route' => $currentRoute,
            'url' => $currentUrl
        ]);
    }
}
```

## Middleware Integration

### 9. **Enhanced Middleware**

```php
class AuthMiddleware
{
    // Automatic dependency injection in middleware
    public function __construct(
        private UserService $userService,
        private SessionInterface $session,
        private Logger $logger
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Use method injection for complex operations
        $user = App::diCall(function(
            TokenInterface $token,
            UserRepository $users
        ) use ($request) {
            $authToken = $request->getHeader('Authorization');
            if (!$authToken || !$token->verify($authToken)) {
                return null;
            }

            return $users->findByToken($authToken);
        });

        if (!$user) {
            return new RedirectResponse('/login');
        }

        // Make user available for injection in controllers
        App::getInstance()->instance('current.user', $user);

        return $next($request);
    }
}

// Controllers can now inject the current user
class DashboardController extends Controller
{
    public function index(User $currentUser): string // Automatically injected
    {
        return $this->render('dashboard', ['user' => $currentUser]);
    }
}
```

## Testing Integration

### 10. **Testing with Container**

```php
class UserServiceTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        $this->app = new App(); // Fresh container for each test

        // Bind test doubles
        $this->app->bind(UserRepository::class, MockUserRepository::class);
        $this->app->bind(Logger::class, NullLogger::class);

        // Set test parameters
        $this->app->setGlobalParameters([
            'debug_mode' => true,
            'testing' => true
        ]);
    }

    public function testCreateUser(): void
    {
        // Use dependency injection in tests
        $service = $this->app->resolve(UserService::class);

        $user = $service->createUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);

        $this->assertInstanceOf(User::class, $user);
    }

    public function testWithMethodInjection(): void
    {
        $result = $this->app->call(function(
            UserService $userService,
            MockUserRepository $repository
        ) {
            // Test with injected dependencies
            return $userService->getAllUsers();
        });

        $this->assertIsArray($result);
    }
}
```

## Performance Tips

### 11. **Optimization Strategies**

```php
// In production bootstrap
$app = App::getInstance();

// Disable auto-wiring for better performance
$app->setAutoWiring(false);

// Use explicit bindings for frequently used services
$app->singleton(UserService::class, function($container) {
    return new UserService(
        $container->resolve(UserRepository::class),
        $container->resolve(Logger::class),
        $container->resolve(EventManagerInterface::class)
    );
});

// Pre-resolve expensive services
$app->instance('expensive.service', $app->resolve(ExpensiveService::class));

// Use aliases for better performance
$frequently_used = [
    UserService::class => 'users',
    ProductService::class => 'products',
    OrderService::class => 'orders'
];

foreach ($frequently_used as $class => $alias) {
    $app->alias($class, $alias);
}
```

## Best Practices

1. **Use constructor injection** for required dependencies
2. **Use method injection** for optional or contextual dependencies
3. **Leverage tagged services** for plugin architectures
4. **Use aliases** for commonly accessed services
5. **Set global parameters** for application-wide configuration
6. **Use factory bindings** for complex object creation
7. **Disable auto-wiring in production** for better performance
8. **Use the static methods** (`App::diGet()`, etc.) for convenience
9. **Bind interfaces to implementations** for better testability
10. **Use contextual binding** for different implementations in different contexts
