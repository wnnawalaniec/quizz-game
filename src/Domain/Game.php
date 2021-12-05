<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use JetBrains\PhpStorm\Pure;

class Game
{
    private function __construct(
        State $state = State::NEW_GAME,
        array $players = [],
        array $questions = [],
        int $currentQuestion = -1,
        array $score = []
    ) {
        $this->state = $state;
        $this->players = $players;
        $this->questions = $questions;
        $this->currentQuestion = $currentQuestion;
        $this->score = $score;
    }

    #[Pure] public static function startNewGame(array $questions): self
    {
        return new self(State::NEW_GAME, [], $questions);
    }

    private State $state;
    /** @var Player[] */
    private array $players;
    /** @var Question[] */
    private array $questions;
    private int $currentQuestion;
    /** @var Score[] */
    private array $score;
}