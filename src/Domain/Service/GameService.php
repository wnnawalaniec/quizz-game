<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Service;

use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Service\Exception\CannotStartNewGameWhenThereIsAlreadyOne;

class GameService
{
    public function __construct(Game\Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws CannotStartNewGameWhenThereIsAlreadyOne
     */
    public function startNewGame(): void
    {
        if ($this->repository->get() !== null) {
            throw CannotStartNewGameWhenThereIsAlreadyOne::create();
        }
        $this->repository->store(Game::startNewGame([]));
    }

    private Game\Repository $repository;
}