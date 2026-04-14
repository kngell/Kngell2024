declare(strict_types=1);

use Framework\Components\Container\Attributes\Inject;

class MyController
{
#[Inject]
public function exportToPdf(
PdfGenerator $pdf,
#[Inject('email.service')]
EmailService $email,
): Response {
// ...
}
}

// Example 1: Method-level Inject (auto-resolve all parameters)
class UserController
{
#[Inject]
public function sendNewsletter(
EmailService $email, // Auto-resolved by type
UserRepository $users, // Auto-resolved by type
LoggerInterface $logger // Auto-resolved by type
): void {
// ...
}
}

// Example 2: Parameter-level Inject with specific binding ID
class OrderController
{
public function process(
#[Inject('payment.gateway.stripe')] PaymentGateway $gateway,
#[Inject('current.user')] User $user
): Response {
// ...
}
}

// Example 3: Mixed - some auto-resolved, some explicit
class ReportController
{
#[Inject]
public function generate(
ReportGenerator $generator, // Auto-resolved
#[Inject('report.config')] array $config, // Specific binding
string $format = 'pdf' // Default value
): Response {
// ...
}
}

// Call the method with injection
$container->call([$controller, 'sendNewsletter']);
$container->call([$controller, 'process']);
$container->call([$controller, 'generate']);