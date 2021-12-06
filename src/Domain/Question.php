<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Wojciech\QuizGame\Domain\Question\Exception\EmptyTextGiven;
use Wojciech\QuizGame\Domain\Question\Exception\NoAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\NoCorrectAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\RepeatingAnswersGiven;
use Wojciech\QuizGame\Domain\Question\Exception\TooManyCorrectAnswersGiven;

/**
 * @Entity
 * @ORM\Table(name="question")
 */
class Question
{
    /**
     * @param Answer[] $answers
     * @throws NoCorrectAnswerGiven
     * @throws NoAnswerGiven
     * @throws TooManyCorrectAnswersGiven
     * @throws EmptyTextGiven
     * @throws RepeatingAnswersGiven
     */
    public function __construct(int $number, string $text, array $answers)
    {
        $this->number = $number;
        $this->text = $text;
        $this->answers = $answers;
        $this->validateAnswers($answers);
        $this->validateText($text);
    }

    /**
     * @param Answer[] $answers
     * @throws NoAnswerGiven
     * @throws NoCorrectAnswerGiven
     * @throws TooManyCorrectAnswersGiven
     * @throws RepeatingAnswersGiven
     */
    protected function validateAnswers(array $answers): void
    {
        if (empty($answers)) {
            throw NoAnswerGiven::create();
        }

        $correct = array_filter($answers, fn ($a) => $a->isCorrect());
        if (count($correct) === 0) {
            throw NoCorrectAnswerGiven::create();
        }

        if (count($correct) > 1) {
            throw TooManyCorrectAnswersGiven::create();
        }

        $answerNumbers = array_map(fn ($a) => $a->number(), $answers);
        if (count(array_unique($answerNumbers)) !== count($answers)) {
            throw RepeatingAnswersGiven::create($this->number);
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

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private int $id;
    /**
     * @ORM\Column(type="integer")
     */
    private int $number;
    /** @ORM\Column(type="string") */
    private string $text;
    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="question")
     * @var Answer[]
     */
    private array|Collection $answers;
    /** @ORM\ManyToOne(targetEntity="Game", inversedBy="questions") */
    private int $game;
}