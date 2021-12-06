<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Wojciech\QuizGame\Application\AuthenticationService;
use Wojciech\QuizGame\Application\Settings;
use Wojciech\QuizGame\Application\Transaction;
use Wojciech\QuizGame\Domain\Game\Repository;
use Wojciech\QuizGame\Domain\Service\GameService;
use Wojciech\QuizGame\Infrastructure\Application\DoctrineTransaction;
use Wojciech\QuizGame\Infrastructure\Application\SessionBasedAuthenticationService;
use Wojciech\QuizGame\Infrastructure\Domain\Game\DoctrineRepository;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(Settings::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        Repository::class => function (ContainerInterface $c) {
            return new DoctrineRepository($c->get(EntityManagerInterface::class));
        },
        GameService::class => function (ContainerInterface $c) {
            return new GameService($c->get(Repository::class));
        },
        Transaction::class => function (ContainerInterface $c) {
            return new DoctrineTransaction($c->get(EntityManagerInterface::class));
        },
        AuthenticationService::class => fn (ContainerInterface $c) => SessionBasedAuthenticationService::getInstance($c->get(Settings::class))
    ]);
};