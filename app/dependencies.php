<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Wojciech\QuizGame\Application\Service\Authentication;
use Wojciech\QuizGame\Application\Service\Persistence;
use Wojciech\QuizGame\Application\Settings;
use Wojciech\QuizGame\Application\UserSession;
use Wojciech\QuizGame\Domain\Game\Repository as GameRepositoryInterface;
use Wojciech\QuizGame\Domain\Player\Repository as PlayerRepositoryInterface;
use Wojciech\QuizGame\Domain\Service\GameService;
use Wojciech\QuizGame\Infrastructure\Application\GlobalUserSession;
use Wojciech\QuizGame\Infrastructure\Application\Service\DoctrinePersistence;
use Wojciech\QuizGame\Infrastructure\Application\Service\SessionBasedAuthentication;
use Wojciech\QuizGame\Infrastructure\Domain\Game\DoctrineRepository as GameRepository;
use Wojciech\QuizGame\Infrastructure\Domain\Player\DoctrineRepository as PlayerRepository;

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
        GameRepositoryInterface::class => function (ContainerInterface $c) {
            return new GameRepository($c->get(EntityManagerInterface::class));
        },
        PlayerRepositoryInterface::class => function (ContainerInterface $c) {
            return new PlayerRepository($c->get(EntityManagerInterface::class));
        },
        GameService::class => function (ContainerInterface $c) {
            return new GameService($c->get(GameRepositoryInterface::class));
        },
        Persistence::class => function (ContainerInterface $c) {
            return new DoctrinePersistence($c->get(EntityManagerInterface::class));
        },
        Authentication::class => fn (ContainerInterface $c) => SessionBasedAuthentication::getInstance(
            $c->get(Settings::class),
            $c->get(UserSession::class)
        ),
        UserSession::class => fn (ContainerInterface $c) => GlobalUserSession::getInstance()
    ]);
};