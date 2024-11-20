<?php

declare(strict_types=1);

require dirname(getcwd()) . '/vendor/autoload.php';
$app = new App();
$app->boot()->run();