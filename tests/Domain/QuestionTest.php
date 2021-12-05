<?php
declare(strict_types=1);

namespace Tests\Wojciech\QuizGame\Domain;

use Tests\Wojciech\QuizGame\BaseTest;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Domain\Question\Exception\EmptyTextGiven;
use Wojciech\QuizGame\Domain\Question\Exception\NoAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\NoCorrectAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\RepeatingAnswersGiven;
use Wojciech\QuizGame\Domain\Question\Exception\TooManyCorrectAnswersGiven;

class QuestionTest extends BaseTest
{
    public function testCreating_NoAnswerGiven_ThrowsException(): void
    {
        $answers = [];

        $act = fn () => $this->createQuestionWithAnswers($answers);

        $expectedException = NoAnswerGiven::create();
        $this->assertException($expectedException, $act);
    }

    public function testCreating_NoCorrectAnswerGiven_ThrowsException(): void
    {
        $answers = [
            Answer::createIncorrect('wrong', 1)
        ];

        $act = fn () => $this->createQuestionWithAnswers($answers);

        $expectedException = NoCorrectAnswerGiven::create();
        $this->assertException($expectedException, $act);
    }

    public function testCreating_MoreThenOneCorrectAnswerGiven_ThrowsException(): void
    {
        $answers = [
            Answer::createCorrect('correct', 1),
            Answer::createCorrect('correct', 2)
        ];

        $act = fn () => $this->createQuestionWithAnswers($answers);

        $expectedException = TooManyCorrectAnswersGiven::create();
        $this->assertException($expectedException, $act);
    }

    public function testCreating_RepeatingAnswerGiven_ThrowsException(): void
    {
        $answerNo = 1;
        $answers = [
            Answer::createIncorrect('wrong', $answerNo),
            Answer::createCorrect('correct', $answerNo)
        ];

        $act = fn () => $this->createQuestionWithAnswers($answers);

        $expectedException = RepeatingAnswersGiven::create(self::QUESTION_NUMBER);
        $this->assertException($expectedException, $act);
    }

    /** @dataProvider emptyTextProvider */
    public function testCreating_EmptyTextGiven_ThrowsException(string $text): void
    {
        $emptyText = $text;

        $act = fn () => new Question(self::QUESTION_NUMBER, $emptyText, [Answer::createCorrect('Answer', 1)]);

        $expectedException = EmptyTextGiven::create();
        $this->assertException($expectedException, $act);
    }

    public function emptyTextProvider(): array
    {
        return [
            [''],
            [' '],
            [PHP_EOL]
        ];
    }

    public function testCreating_AnswersAndTextGiven_QuestionCreated(): void
    {
        $answers = [
            Answer::createIncorrect('wrong', 1),
            Answer::createCorrect('correct', 2)
        ];
        $text = self::QUESTION;
        $number = self::QUESTION_NUMBER;

        new Question($number, $text, $answers);

        $this->assertNoExceptions();
    }

    private function createQuestionWithAnswers(array $answers): Question
    {
        return new Question(self::QUESTION_NUMBER, self::QUESTION, $answers);
    }

    const QUESTION = 'Question?';
    const QUESTION_NUMBER = 1;
}
