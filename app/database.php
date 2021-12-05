<?php
declare(strict_types=1);

use BenTools\Doctrine\NativeEnums\Type\NativeEnum;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Psr\Container\ContainerInterface;
use Wojciech\QuizGame\Application\Settings;
use Wojciech\QuizGame\Domain\State;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        EntityManagerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(Settings::class);

            $config = Setup::createAnnotationMetadataConfiguration(
                $settings->get('doctrine_entities'),
                $settings->get('dev'),
                null,
                null,
                false
            );

            NativeEnum::registerEnumType(State::class);

            return EntityManager::create($settings->get('database'), $config);
        }
    ]);
};