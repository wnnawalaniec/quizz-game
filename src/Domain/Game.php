<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @ORM\Entity()
 * @ORM\Table(name="game")
 */
class Game implements \JsonSerializable
{
    private function __construct(
        State $state,
        array $players = [],
        array $questions = [],
        array $score = [],
        int $currentQuestion = -1
    ) {
        $this->state = $state;
        $this->players = new ArrayCollection($players);
        $this->questions = new ArrayCollection($questions);
        $this->currentQuestion = $currentQuestion;
        $this->score = new ArrayCollection($score);
    }

    public static function startNewGame(Question...$questions): self
    {
        return new self(State::NEW_GAME, [], $questions);
    }

    public function addQuestion(Question $question): void
    {
        $question->setGame($this);
        $this->questions->add($question);
    }

    public function questions(): Collection
    {
        return $this->questions;
    }

    #[ArrayShape([
        'id' => "int",
        'state' => "\Wojciech\QuizGame\Domain\State",
        'questions' => "mixed",
        'players' => "mixed"
    ])]
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
    private int $id = 1; // This is hack to simply keep only one instance of game
    /** @ORM\Column(type= State::class) */
    private State $state;
    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="game", cascade={"persist", "remove"})
     * @var Collection<Player>
     */
    private Collection $players;
    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="game", cascade={"persist", "remove"})
     * @var Collection<Question>
     */
    private Collection $questions;
    /** @ORM\Column(type="integer") */
    private int $currentQuestion;
    /** @var Collection<Score> */
    private Collection $score;
}