<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Application\Service;

use Doctrine\ORM\EntityManagerInterface;
use Wojciech\QuizGame\Application\Service\Persistence;

class DoctrinePersistence implements Persistence
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function begin(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->commit();
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function rollback(): void
    {
        $this->entityManager->rollback();
    }

    private EntityManagerInterface $entityManager;
}