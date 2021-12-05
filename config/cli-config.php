<?php
declare(strict_types=1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/../app/bootstrap.php';

$entityManager = $container->get(\Doctrine\ORM\EntityManagerInterface::class);

return ConsoleRunner::createHelperSet($entityManager);