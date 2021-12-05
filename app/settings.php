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
                'dev' => $_ENV['DEV'],
                'displayErrorDetails' => $_ENV['DISPLAY_ERROR_DETAILS'], // Should be set to false in production
                'logError'            => $_ENV['LOG_ERROR'],
                'logErrorDetails'     => $_ENV['LOG_ERROR_DETAILS'],
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