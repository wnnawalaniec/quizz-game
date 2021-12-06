<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity()
 * @ORM\Table(name="game")
 */
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

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $id = 1;

    /** @ORM\Column(type= State::class) */
    private State $state;
    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="game")
     * @var Player[]
     */
    private array|Collection $players;
    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="game")
     * @var Question[]
     */
    private array|Collection $questions;
    /** @ORM\Column(type="integer") */
    private int $currentQuestion;
    /** @var Score[] */
    private array|Collection $score;
}