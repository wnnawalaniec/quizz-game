<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;
use Wojciech\QuizGame\Application\Settings;
use Wojciech\QuizGame\Infrastructure\Application\BasicSettings;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        Settings::class => function () {
            return new BasicSettings([
                'dev' => filter_var($_ENV['DEV'], FILTER_VALIDATE_BOOL),
                'displayErrorDetails' => filter_var($_ENV['DISPLAY_ERROR_DETAILS'], FILTER_VALIDATE_BOOL), // Should be set to false in production
                'logError'            => filter_var($_ENV['LOG_ERROR'], FILTER_VALIDATE_BOOL),
                'logErrorDetails'     => filter_var($_ENV['LOG_ERROR_DETAILS'], FILTER_VALIDATE_BOOL),
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'database' => [
                    'driver'   => 'pdo_mysql',
                    'user'     => $_ENV['MYSQL_USER'],
                    'password' => $_ENV['MYSQL_PASSWORD'],
                    'dbname'   => $_ENV['MYSQL_DATABASE'],
                    'host'     => $_ENV['MYSQL_HOST']
                ],
                'doctrine_entities' =>[ __DIR__ . '/../src']
            ]);
        }
    ]);
};