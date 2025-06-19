<?php

declare(strict_types=1);

// This is a simple test file to verify method injection

require_once __DIR__ . '/vendor/autoload.php';

use App\HTMLComponents\Blog\BlogPostHTMLElement;
use Framework\Components\Container\Attributes\Inject;

// Set up a mock user
$mockUser = new User();
$mockUser->firstname = 'Test';
$mockUser->lastname = 'User';

// Register the user in the container
App::getInstance()->instance('current.user', $mockUser);

// Create a blog post component
$blogComponent = new BlogPostHTMLElement([], new HtmlBuilder());

// Method 1: Call directly (no injection)
echo "Method 1: Direct call (no injection)\n";
$result1 = $blogComponent->display();
var_dump($result1);

// Method 2: Call with MethodInjection (should inject)
echo "\nMethod 2: Using MethodInjection\n";
$result2 = MethodInjection::invoke($blogComponent, 'display');
var_dump($result2);

echo "\nTest completed!\n";