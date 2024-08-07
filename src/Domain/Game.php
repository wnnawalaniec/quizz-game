<?php

declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Wojciech\QuizGame\Domain\Game\Exception\AlreadyScored;
use Wojciech\QuizGame\Domain\Game\Exception\AnswerIsNotForCurrentQuestion;
use Wojciech\QuizGame\Domain\Game\Exception\CannotAddQuestionGameIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotJoinGameWhichIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotStartGame;
use Wojciech\QuizGame\Domain\Game\Exception\GameIsFinished;
use Wojciech\QuizGame\Domain\Game\Exception\GameIsNotFinished;
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

    /**
     * @throws CannotAddQuestionGameIsNotNew
     */
    public function removeQuestion(int $questionIdx): void
    {
        if (!$this->isNewGame()) {
            throw CannotAddQuestionGameIsNotNew::create();
        }

        foreach ($this->questions as $question) {
            if ($question->id() === $questionIdx) {
                $this->questions->removeElement($question);
                return;
            }
        }
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

    /**
     * @return Collection<Player>
     */
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
     * @throws GameIsFinished
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

        if ($this->allPlayersAnswered()) {
            if ($this->isLastQuestion()) {
                $this->state = $this->state->nextStage();
            }
        }
    }

    public function scores(): Collection
    {
        return $this->scores;
    }

    /**
     * @throws GameIsNotFinished
     */
    public function results(): array
    {
        if (!$this->isFinished()) {
            throw GameIsNotFinished::create();
        }

        $results = [];
        foreach ($this->scores as $score) {
            $correct = $results['scores'][$score->player()->id()] ?? 0;
            $results['scores'][$score->player()->id()]['score'] = $correct + (int)$score->answer()->isCorrect();
            $results['scores'][$score->player()->id()]['name'] = $score->player()->name();
        }

        $results['questions'] = $this->questions->count();

        return $results;
    }

    /**
     * @throws GameNotStarted
     * @throws GameIsFinished
     */
    public function currentQuestion(): Question
    {
        if ($this->isFinished()) {
            throw GameIsFinished::create();
        }
        if (!$this->isStarted()) {
            throw GameNotStarted::create();
        }

        $id = (int)($this->scores->count() / $this->players()->count());
        return $this->questions()->get($id);
    }

    private function isLastQuestion(): bool
    {
        return (int)$this->scores->count() / $this->players()->count() === $this->questions->count();
    }

    private function allPlayersAnswered(): bool
    {
        return $this->scores->count() % $this->players()->count() === 0;
    }

    #[Pure] private function isFinished(): bool
    {
        return $this->state() === State::FINISHED;
    }

    public function hasAnsweredCurrentQuestion(Player $player): bool
    {
        return !$this
            ->scores
            ->filter(
                fn(Score $score) => $score->player()->equals($player)
                    && $score->question()->equals($this->currentQuestion())
            )
            ->isEmpty();
    }

    public function hasWon(Player $player): bool
    {
        $scores = $this->playersScores();
        $highestScores = array_keys($scores, max($scores));
        return in_array($player->id(), $highestScores);
    }

    #[Pure] protected function playersScores(): array
    {
        $scores = [];
        foreach ($this->scores as $score) {
            $correctAnswers = ($scores[$score->player()->id()] ?? 0) + (int)$score->answer()->isCorrect();
            $scores[$score->player()->id()] = $correctAnswers;
        }
        return $scores;
    }

    #[Pure] public function playerScore(Player $player): int
    {
        return $this->playersScores()[$player->id()];
    }

    #[ArrayShape([
        'id' => "string",
        'state' => "\Wojciech\QuizGame\Domain\State",
        'questions' => "mixed",
        'players' => "mixed"
    ])]
    public function jsonSerialize(): array
    {
        $playersScores = $this->playersScores();
        return [
            'id' => (string)$this->id,
            'state' => $this->state->value,
            'questions' => array_map(fn(Question $q) => $q->jsonSerialize(), $this->questions->toArray()),
            'players' => array_map(fn(Player $p) => $p->jsonSerialize(), $this->players->toArray()),
            'scores' => array_map(fn($s, $p) => ['score' => $s, 'player' => $p],
                array_values($playersScores),
                array_keys($playersScores))
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
     * @ORM\OneToMany(targetEntity="Player", mappedBy="game", cascade={"persist", "remove"}, fetch="EAGER")
     * @var Collection<Player>
     */
    private Collection $players;
    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="game", cascade={"persist", "remove"}, fetch="EAGER", orphanRemoval=true)
     * @var Collection<Question>
     */
    private Collection $questions;
    /**
     * @ORM\OneToMany(targetEntity="Score", mappedBy="game", cascade={"persist", "remove"}, fetch="EAGER")
     * @var Collection<Score>
     */
    private Collection $scores;
}