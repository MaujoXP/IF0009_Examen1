<?php

use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../../vendor/autoload.php';

$container = new Container();

$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
$dotenv->load();

AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

require __DIR__ . '/routes.php';
require __DIR__ . '/config.php';

$app->addErrorMiddleware(false, false, false);

$app->run();
