<?php
declare(strict_types=1);

use BenTools\Doctrine\NativeEnums\Type\NativeEnum;
use DI\ContainerBuilder;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Psr\Container\ContainerInterface;
use Wojciech\QuizGame\Application\Settings;
use Wojciech\QuizGame\Domain\Game\State;

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
            Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');

            return EntityManager::create($settings->get('database'), $config);
        }
    ]);
};