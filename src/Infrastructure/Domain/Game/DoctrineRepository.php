<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Domain\Game;

use Doctrine\ORM\EntityManagerInterface;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Game\Repository;

class DoctrineRepository implements Repository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function store(Game $game): void
    {
        $this->entityManager->persist($game);
    }

    public function get(): ?Game
    {
        $games = $this->entityManager->getRepository(Game::class)->findAll();
        if (empty($games)) {
            return null;
        }

        return $games[0];
    }

    public function clear(): void
    {
        foreach ($this->entityManager->getRepository(Game::class)->findAll() as $game) {
            $this->entityManager->remove($game);
        }
    }

    private EntityManagerInterface $entityManager;
}