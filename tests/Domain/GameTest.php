<?php
declare(strict_types=1);

namespace Tests\Wojciech\QuizGame\Domain;

use PHPUnit\Framework\TestCase;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Question;

class GameTest extends TestCase
{
    public function testAddingQuestion_GameIsNew_QuestionAdded(): void
    {
        $expectedQuestion = $this->createQuestion();
        $game = Game::startNewGame();
        $game->addQuestion($expectedQuestion);

        $this->assertContains($expectedQuestion, $game->questions()->toArray());
    }

    protected function createAnswers(): array
    {
        return [Answer::createCorrect('1'), Answer::createIncorrect('1')];
    }

    protected function createQuestion(): Question
    {
        return new Question('q', ...$this->createAnswers());
    }
}
