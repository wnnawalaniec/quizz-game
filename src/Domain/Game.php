<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Wojciech\QuizGame\Domain\Game\Exception\AlreadyScored;
use Wojciech\QuizGame\Domain\Game\Exception\AnswerIsNotForCurrentQuestion;
use Wojciech\QuizGame\Domain\Game\Exception\CannotAddQuestionGameIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotJoinGameWhichIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotStartGame;
use Wojciech\QuizGame\Domain\Game\Exception\GameNotStarted;
use Wojciech\QuizGame\Domain\Game\Exception\PlayerIsNotSupposedThisGame;
use Wojciech\QuizGame\Domain\Game\State;

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
        array $score = []
    ) {
        $this->state = $state;
        $this->players = new ArrayCollection($players);
        $this->questions = new ArrayCollection($questions);
        $this->currentQuestion = -1;
        $this->scores = new ArrayCollection($score);
    }

    public static function createNewGame(Question...$questions): self
    {
        return new self(State::NEW_GAME, [], $questions);
    }

    /**
     * @throws CannotAddQuestionGameIsNotNew
     */
    public function addQuestion(Question $question): void
    {
        if (!$this->isNewGame()) {
            throw CannotAddQuestionGameIsNotNew::create();
        }

        $question->setGame($this);
        $this->questions->add($question);
    }

    public function questions(): Collection
    {
        return $this->questions;
    }

    /**
     * @throws CannotStartGame
     * @throws Game\Exception\GameIsFinished
     */
    public function start(): void
    {
        if ($this->isStarted()) {
            return;
        }

        if ($this->questions->isEmpty()) {
            throw CannotStartGame::createForNoQuestions();
        }

        if ($this->players->isEmpty()) {
            throw CannotStartGame::createForNoPlayers();
        }

        $this->state = $this->state->nextStage();
        $this->currentQuestion = 0;
    }

    public function state(): State
    {
        return $this->state;
    }

    /**
     * @throws CannotJoinGameWhichIsNotNew
     */
    public function join(Player $player): void
    {
        if (!$this->isNewGame()) {
            throw CannotJoinGameWhichIsNotNew::create();
        }

        $player->setGame($this);
        $this->players->add($player);
    }

    public function players(): Collection
    {
        return $this->players;
    }

    protected function isNewGame(): bool
    {
        return $this->state === State::NEW_GAME;
    }

    protected function isStarted(): bool
    {
        return $this->state === State::STARTED;
    }

    public function id(): string
    {
        return $this->id;
    }

    /**
     * @throws AnswerIsNotForCurrentQuestion
     * @throws PlayerIsNotSupposedThisGame
     * @throws GameNotStarted
     * @throws AlreadyScored
     */
    public function score(Player $player, Answer $answer): void
    {
        if (!$this->isStarted()) {
            throw GameNotStarted::create();
        }

        if (!$this->players->contains($player)) {
            throw PlayerIsNotSupposedThisGame::create();
        }

        if (!$this->currentQuestion()->answers()->contains($answer)) {
            throw AnswerIsNotForCurrentQuestion::create();
        }

        foreach ($this->scores as $score) {
            if (
                $score->question()->equals($this->currentQuestion())
                && $score->player()->equals($player)
            ) {
                throw AlreadyScored::create();
            }
        }

        $this->scores->add(new Score($this, $player, $this->currentQuestion(), $answer));
    }

    public function scores(): Collection
    {
        return $this->scores;
    }

    /**
     * @throws GameNotStarted
     */
    public function currentQuestion(): Question
    {
        if (!$this->isStarted()) {
            throw GameNotStarted::create();
        }
        return $this->questions->toArray()[$this->currentQuestion];
    }

    #[ArrayShape([
        'id' => "string",
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
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private string $id;
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
    /**
     * @ORM\OneToMany(targetEntity="Score", mappedBy="game", cascade={"persist", "remove"})
     * @var Collection<Score>
     */
    private Collection $scores;
}