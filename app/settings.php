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
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
            ]);
        }
    ]);
};