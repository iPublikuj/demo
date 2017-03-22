<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Events\WebSocketsClientDisconnectHandler;
use IPub\WebSockets\Server\Wrapper;

define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', __DIR__ . DS . '..');
define('WWW_DIR', BASE_DIR . DS . 'web');

$configurator = new Nette\Configurator;

$configurator->setDebugMode(['89.203.133.96', '82.202.112.233']);
$configurator->enableTracy(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../tmp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');
// Define variables
$configurator->addParameters([
	"baseDir"			=> BASE_DIR,
	"wwwDir"			=> realpath(WWW_DIR),
	"locale"			=> "en_US",
]);

$container = $configurator->createContainer();

$webSocketsCloseEvent = $container->getByType(WebSocketsClientDisconnectHandler::class);

$webSocketsApp = $container->getByType(Wrapper::class);
$webSocketsApp->onClientDisconnected[] = [$webSocketsCloseEvent, '__invoke'];

return $container;
