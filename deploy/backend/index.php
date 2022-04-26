<?php
namespace kv6002;

/* Register Panic
-------------------------------------------------- */

// Disable 'lesser' errors until we know whether to show them (from the Config)
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);

require_once "panic.php";
registerPanic();

/* Load and Initialise Dependencies
-------------------------------------------------- */

require_once "autoloader.php";

use util\Util;
use dispatcher\Dispatcher;
use router\resource\Pathfinder;
use router\Router;
use router\Request;
use database\Database;
use logger\FileLogger;
use kv6002\standard\ErrorResource;
use kv6002\standard\JWTAuthenticator;

use kv6002\resources;

// Get config and set error level
$config = new Config(__dir__);
if ($config->get('development')) {
    error_reporting(E_ALL);
}

$request = Request::fromPHPGlobalState();

// Set up internal resources
$pathfinder = new Pathfinder(
    $config->get('server_protocol'),
    $config->get('server_host'),
    $config->get('server_root'),
    $config->get('application_root')
);

$logger = new FileLogger(
    $pathfinder->internalPathFor($config->get('logfile'))
);

$db = new Database($config->get('database_info'), true);

$authenticator = new JWTAuthenticator(
    $config->get('jwt_secret'),
    $pathfinder->urlFor("/api/auth"),
    $db
);

// Initialise the router and set an even 'prettier' global error handler than
// the one Router sets.
$router = new Router($request, $pathfinder, $logger);
$router->registerGlobalError(
    new ErrorResource($pathfinder, $config->get('development'))
);

/* Define Web Resources
-------------------------------------------------- */

// Including any endpoint-specific error resources

$router->register("/api/ping", new resources\Ping());

$router->register("/api/auth", new resources\Authenticate($db, $authenticator));
$router->register("/api/change-password",
    new resources\PasswordReset($db, $authenticator)
);

$garageConsultantsRes = new resources\GarageConsultants($db, $authenticator);
$router->register("/api/garage-consultants", $garageConsultantsRes);
$router->register("/api/garage-consultants/:id<int>", $garageConsultantsRes);

$garageRes = new resources\Garages($db, $authenticator);
$router->register("/api/garages", $garageRes);
$router->register("/api/garages/:id<int>", $garageRes);

$instrumentRes = new resources\Instruments($db, $authenticator);
$router->register("/api/instruments", $instrumentRes);
$router->register("/api/instruments/:id<int>", $instrumentRes);

$contactRes = new resources\ContactMessages($db, $authenticator);
$router->register("/api/contact-messages", $contactRes);

$router->register("/api/send-emails", new resources\Emails($db));

/* Dispatch Request
-------------------------------------------------- */

$router->dispatch();
