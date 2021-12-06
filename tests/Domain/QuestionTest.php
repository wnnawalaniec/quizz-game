<?php
declare(strict_types=1);

namespace Tests\Wojciech\QuizGame\Domain;

use Tests\Wojciech\QuizGame\BaseTest;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Domain\Question\Exception\EmptyTextGiven;
use Wojciech\QuizGame\Domain\Question\Exception\NoAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\NoCorrectAnswerGiven;
use Wojciech\QuizGame\Domain\Question\Exception\OnlyOneAnswerGiven;
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
            Answer::createIncorrect('wrong 1'),
            Answer::createIncorrect('wrong 2')
        ];

        $act = fn () => $this->createQuestionWithAnswers($answers);

        $expectedException = NoCorrectAnswerGiven::create();
        $this->assertException($expectedException, $act);
    }

    public function testCreating_MoreThenOneCorrectAnswerGiven_ThrowsException(): void
    {
        $answers = [
            Answer::createCorrect('correct 1'),
            Answer::createCorrect('correct 2')
        ];

        $act = fn () => $this->createQuestionWithAnswers($answers);

        $expectedException = TooManyCorrectAnswersGiven::create();
        $this->assertException($expectedException, $act);
    }

    /** @dataProvider emptyTextProvider */
    public function testCreating_EmptyTextGiven_ThrowsException(string $text): void
    {
        $emptyText = $text;

        $answers = [Answer::createCorrect('correct 1'), Answer::createIncorrect('wrong 1')];
        $act = fn () => new Question($emptyText, ...$answers);

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
            Answer::createIncorrect('wrong'),
            Answer::createCorrect('correct')
        ];
        $text = self::QUESTION;

        new Question($text, ...$answers);

        $this->assertNoExceptions();
    }

    public function testCreating_SingleAnswerGiven_ThrowsException(): void
    {
        $answers = [
            Answer::createCorrect('test')
        ];

        $act = fn () => new Question(self::QUESTION, ...$answers);

        $expectedException = OnlyOneAnswerGiven::create();
        $this->assertException($expectedException, $act);
    }

    private function createQuestionWithAnswers(array $answers): Question
    {
        return new Question(self::QUESTION, ...$answers);
    }

    const QUESTION = 'Question?';
}
