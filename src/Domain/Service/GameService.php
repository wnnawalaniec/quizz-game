<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Service;

use Doctrine\Common\Collections\Collection;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Game\Exception\CannotStartGame;
use Wojciech\QuizGame\Domain\Game\Exception\GameIsFinished;
use Wojciech\QuizGame\Domain\Game\Exception\GameNotStarted;
use Wojciech\QuizGame\Domain\Player;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Domain\Service\Exception\CannotStartNewGameWhenThereIsAlreadyOne;
use Wojciech\QuizGame\Domain\Service\Exception\NoGameExists;

class GameService
{
    public function __construct(Game\Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws NoGameExists
     */
    public function game(): Game
    {
        $game = $this->repository->get();
        if ($game === null) {
            throw NoGameExists::create();
        }
        return $game;
    }

    /**
     * @throws CannotStartNewGameWhenThereIsAlreadyOne
     */
    public function createNew(): void
    {
        $game = $this->repository->get();

        if ($game) {
            if ($game->state() !== Game\State::FINISHED) {
                throw CannotStartNewGameWhenThereIsAlreadyOne::create();
            }

            $this->repository->clear();
        }
        $this->repository->store(Game::createNewGame());
    }

    /**
     * @throws CannotStartGame
     * @throws GameIsFinished
     * @throws NoGameExists
     */
    public function start(): void
    {
        $game = $this->repository->get();
        if ($game === null) {
            throw NoGameExists::create();
        }

        $game->start();
    }

    /**
     * @throws NoGameExists
     * @throws Game\Exception\CannotAddQuestionGameIsNotNew
     */
    public function addQuestion(Question $question): void
    {
        $game = $this->repository->get();
        if ($game === null) {
            throw NoGameExists::create();
        }
        $game->addQuestion($question);
    }

    /**
     * @throws Game\Exception\CannotJoinGameWhichIsNotNew
     * @throws NoGameExists
     */
    public function join(Player $player): void
    {
        $game = $this->repository->get();
        if ($game === null) {
            throw NoGameExists::create();
        }
        $game->join($player);
    }

    /**
     * @return Collection<Question>
     * @throws NoGameExists
     */
    public function questions(): Collection
    {
        $game = $this->repository->get();
        if ($game === null) {
            throw NoGameExists::create();
        }
        return $game->questions();
    }

    /**
     * @throws GameNotStarted
     * @throws NoGameExists
     * @throws GameIsFinished
     */
    public function currentQuestion(): Question
    {
        $game = $this->repository->get();
        if ($game === null) {
            throw NoGameExists::create();
        }
        return $game->currentQuestion();
    }

    private Game\Repository $repository;
}