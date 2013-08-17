<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Controllers', __DIR__ . '/../app');
$loader->add('Models', __DIR__ . '/../app');
$loader->add('Services', __DIR__ . '/../app');
$loader->add('Utils', __DIR__ . '/../app');

ErrorHandler::register();
ExceptionHandler::register();

$app = new Silex\Application();

$app['debug'] = true;


// dependency injection

// the object responsible for converting event details between various
// data formats including JSON, XML, database rows, and PHP class objects
$app['event.converter'] = $app->share(function ($app) {
  return new Services\Converter($app, 'event', 'Models\Event');
});

$app['event.repository'] = $app->share(function ($app) {
  return new Services\DbService(
    $app['db'],                     // database connection
    $app['event.converter'],        // converts between various data formats
    $app['monolog'],                // logging utility
    $app['debug'],                  // debug flag
    25                              // default page size
  );
});

$app['event.controller'] = $app->share(function ($app) {
  return new Controllers\Events(
    $app['event.repository'],       // repository manager
    $app['event.converter'],        // converts between various data formats
    $app['serializer'],             // xml serializer
    $app['validator'],              // validation utility
    $app['monolog'],                // logging utility
    $app['debug']                   // debug flag
  );
});

// the object responsible for converting market details between various
// data formats including JSON, XML, database rows, and PHP class objects
$app['market.converter'] = $app->share(function ($app) {
  return new Services\Converter($app, 'market', 'Models\Market');
});

$app['market.repository'] = $app->share(function ($app) {
  return new Services\DbService(
    $app['db'],                     // database connection
    $app['market.converter'],       // converts between various data formats
    $app['monolog'],                // logging utility
    $app['debug'],                  // debug flag
    25                              // default page size
  );
});

$app['market.controller'] = $app->share(function ($app) {
  return new Controllers\Markets(
    $app['market.repository'],      // repository manager
    $app['market.converter'],       // converts between various data formats
    $app['serializer'],             // xml serializer
    $app['validator'],              // validation utility
    $app['monolog'],                // logging utility
    $app['debug']                   // debug flag
  );
});


// service providers

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options' => array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'tipper',
    'user' => '',
    'charset' => 'utf8',
  ),
));

$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => __DIR__ . '/../logs/development.log',
));

$app->register(new JDesrosiers\Silex\Provider\SwaggerServiceProvider(), array(
  'swagger.srcDir' => __DIR__ . '/../vendor/zircote/swagger-php/library',
  'swagger.servicePath' => __DIR__,
));

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// for xml serialization
$app->register(new Silex\Provider\SerializerServiceProvider());

// For more customizable serialization using annotations
// see http://jmsyst.com/libs/serializer/master/reference/annotations

$app->register(new Silex\Provider\ValidatorServiceProvider());


// middleware

// allow CORS
$app->after(function (Request $request, Response $response) {
  $response->headers->add(array(
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Headers' => 'Cache-Control, Pragma, Origin, Authorization, Content-Type, X-Requested-With',
    'Access-Control-Allow-Methods' => 'GET, PUT, POST, OPTIONS',
  ));
});

return $app;
