<?php
declare(strict_types=1);

namespace Tests\Wojciech\QuizGame\Domain;

use Tests\Wojciech\QuizGame\BaseTest;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Game\Exception\CannotAddQuestionGameIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotJoinGameWhichIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotStartGame;
use Wojciech\QuizGame\Domain\Player;
use Wojciech\QuizGame\Domain\Question;

class GameTest extends BaseTest
{
    public function testAddingQuestion_GameIsNew_QuestionAdded(): void
    {
        $expectedQuestion = $this->createQuestion();
        $game = Game::createNewGame();
        $game->addQuestion($expectedQuestion);

        $this->assertContains($expectedQuestion, $game->questions()->toArray());
    }

    public function testAddingQuestion_GameIsNotNew_ThrowsException(): void
    {
        $game = $this->createStartedGame();

        $act = fn () => $game->addQuestion($this->createQuestion());

        $expectedException = CannotAddQuestionGameIsNotNew::create();
        $this->assertException($expectedException, $act);
    }

    public function testStart_GameIsNew_GameStarted(): void
    {
        $game = $this->createNewGame();
        $game->join($this->createPlayer());

        $game->start();

        $this->assertEquals(Game\State::STARTED, $game->state());
    }

    public function testStart_GameIsStarted_GameStarted(): void
    {
        $game = new Game(Game\State::STARTED);

        $game->start();

        $this->assertEquals(Game\State::STARTED, $game->state());
    }

    public function testStart_GameHasNoQuestions_ThrowsException(): void
    {
        $game = Game::createNewGame();

        $act = fn () => $game->start();

        $expectedException = CannotStartGame::createForNoQuestions();
        $this->assertException($expectedException, $act);
    }

    public function testStart_NoPlayerJoined_ThrowsException(): void
    {
        $game = $this->createNewGame();

        $act = fn () => $game->start();

        $expectedException = CannotStartGame::createForNoPlayers();
        $this->assertException($expectedException, $act);
    }

    public function testJoin_GameIsNew_PlayerJoined(): void
    {
        $expectedPlayers = $this->createPlayer();
        $game = $this->createNewGame();

        $game->join($expectedPlayers);

        $this->assertContains($expectedPlayers, $game->players()->toArray());
    }

    public function testJoin_GameIsStarted_ThrowsException(): void
    {
        $game = $this->createStartedGame();

        $act = fn () => $game->join($this->createPlayer());

        $expectedException = CannotJoinGameWhichIsNotNew::create();
        $this->assertException($expectedException, $act);
    }

    protected function createAnswers(): array
    {
        return [Answer::createCorrect('1'), Answer::createIncorrect('1')];
    }

    protected function createQuestion(): Question
    {
        return new Question('q', ...$this->createAnswers());
    }

    protected function createPlayer(): Player
    {
        return new Player('Jhon');
    }

    protected function createStartedGame(): Game
    {
        $game = Game::createNewGame($this->createQuestion());
        $game->join($this->createPlayer());
        $game->start();
        return $game;
    }

    protected function createNewGame(): Game
    {
        return Game::createNewGame($this->createQuestion());
    }
}
