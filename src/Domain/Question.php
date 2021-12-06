<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use JetBrains\PhpStorm\ArrayShape;
use Wojciech\QuizGame\Domain\Question\Exception\EmptyTextGiven;
use Wojciech\QuizGame\Domain\Question\Exception\NoAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\NoCorrectAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\OnlyOneAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\TooManyCorrectAnswersGiven;

/**
 * @Entity
 * @ORM\Table(name="question")
 */
class Question implements \JsonSerializable
{
    /**
     * @throws NoCorrectAnswerGiven
     * @throws NoAnswerGiven
     * @throws TooManyCorrectAnswersGiven
     * @throws EmptyTextGiven
     * @throws OnlyOneAnswerGiven
     */
    public function __construct(string $text, Answer...$answers)
    {
        $this->text = $text;
        foreach ($answers as $answer) {
            $answer->setQuestion($this);
        }
        $this->answers = new ArrayCollection($answers);
        $this->validateAnswers($answers);
        $this->validateText($text);
    }

    /**
     * @throws NoAnswerGiven
     * @throws NoCorrectAnswerGiven
     * @throws TooManyCorrectAnswersGiven
     * @throws OnlyOneAnswerGiven
     */
    protected function validateAnswers(array $answers): void
    {
        if (empty($answers)) {
            throw NoAnswerGiven::create();
        }

        if (count($answers) === 1) {
            throw OnlyOneAnswerGiven::create();
        }

        $correct = array_filter($answers, fn ($a) => $a->isCorrect());
        if (count($correct) === 0) {
            throw NoCorrectAnswerGiven::create();
        }

        if (count($correct) > 1) {
            throw TooManyCorrectAnswersGiven::create();
        }
    }

    /**
     * @throws EmptyTextGiven
     */
    protected function validateText(string $text): void
    {
        if (empty(trim($text))) {
            throw EmptyTextGiven::create();
        }
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    #[ArrayShape([
        'id' => "int",
        'text' => "string",
        'answers' => "mixed"
    ])]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'answers' => array_map(fn (Answer $a) => $a->jsonSerialize(), $this->answers->toArray())
        ];
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private int $id;
    /** @ORM\Column(type="string") */
    private string $text;
    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="question", cascade={"persist", "remove"})
     * @var Collection<Answer>
     */
    private Collection $answers;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="questions")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private Game $game;
}