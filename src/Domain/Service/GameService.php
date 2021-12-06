<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Service;

use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Domain\Service\Exception\CannotAddQuestionGameNotCreatedYet;
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
        $this->repository->store(Game::createNewGame());
    }

    /**
     * @throws CannotAddQuestionGameNotCreatedYet
     */
    public function addQuestion(Question $question): void
    {
        if ($this->repository->get() === null) {
            throw CannotAddQuestionGameNotCreatedYet::create();
        }
        $this->repository->get()->addQuestion($question);
    }

    private Game\Repository $repository;
}