<?php
$startTime = microtime(true);
require_once __DIR__ . '/../vendor/autoload.php';

// This is the default config. See `deploy_config/README.md' for more info.
$config = array(
    'debug' => true,
    'timer.start' => $startTime,
    'monolog.name' => 'silex-bootstrap',
    'monolog.level' => \Monolog\Logger::DEBUG,
    'monolog.logfile' => __DIR__ . '/log/app.log',
    'twig.path' => __DIR__ . '/../src/App/views',
    'twig.options' => array(
        'cache' => __DIR__ . '/cache/twig',
    ),
);

// Apply custom config if available
if (file_exists(__DIR__ . '/config.php')) {
    include __DIR__ . '/config.php';
}


// Initialize Application
$app = new App\Silex\Application($config);


// Use Yaml
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/config/parameters.yml'));
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'params.google' => $app['config']['google']
));

/**
 * Register controllers as services
 * @link http://silex.sensiolabs.org/doc/providers/service_controller.html
 **/
$app['app.default_controller'] = $app->share(
    function () use ($app) {
        return new \App\Controller\DefaultController($app['twig'], $app['logger'], $app['config']);
    }
);
$app['app.calendar_controller'] = $app->share(
    function () use ($app) {
        return new \App\Controller\CalendarController($app['twig'], $app['logger'], $app['config']);
    }
);

// Map routes to controllers
include __DIR__ . '/routing.php';

return $app;
