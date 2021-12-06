<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity()
 * @ORM\Table(name="game")
 */
class Game implements \JsonSerializable
{
    private function __construct(
        State $state = State::NEW_GAME,
        array $players = [],
        array $questions = [],
        int $currentQuestion = -1,
        array $score = []
    ) {
        $this->state = $state;
        $this->players = new ArrayCollection();
        foreach ($players as $player) {
            $this->players->add($player);
        }
        $this->questions = new ArrayCollection();
        foreach ($questions as $question) {
            $this->questions->add($question);
        }
        $this->currentQuestion = $currentQuestion;
        $this->score = $score;
    }

    #[Pure] public static function startNewGame(array $questions): self
    {
        return new self(State::NEW_GAME, [], $questions);
    }

    public function addQuestion(Question $question): void
    {
        $question->setGame($this);
        $this->questions->add($question);
    }

    /**
     * @return array|Collection
     */
    public function questions(): array|Collection
    {
        return $this->questions;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'state' => $this->state,
            'questions' => array_map(fn (Question $q) => $q->jsonSerialize(), $this->questions->toArray()),
            'players' => array_map(fn (Question $q) => $q->jsonSerialize(), $this->players->toArray()),
        ];
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $id = 1;
    /** @ORM\Column(type= State::class) */
    private State $state;
    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="game", cascade={"persist", "remove"})
     * @var Player[]
     */
    private array|Collection $players;
    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="game", cascade={"persist", "remove"})
     * @var Question[]
     */
    private array|Collection $questions;
    /** @ORM\Column(type="integer") */
    private int $currentQuestion;

    /** @var Score[] */
    private array|Collection $score;
}