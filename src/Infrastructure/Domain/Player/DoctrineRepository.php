<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Domain\Player;

use Doctrine\ORM\EntityManagerInterface;
use Wojciech\QuizGame\Domain\Player;
use Wojciech\QuizGame\Domain\Player\Repository;

class DoctrineRepository implements Repository
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(string $id): ?Player
    {
        return $this->entityManager->getRepository(Player::class)->find($id);
    }

    private EntityManagerInterface $entityManager;
}