<?php
declare(strict_types=1);

namespace Tests\Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Tests\Wojciech\QuizGame\BaseTest;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Game\Exception\AlreadyScored;
use Wojciech\QuizGame\Domain\Game\Exception\AnswerIsNotForCurrentQuestion;
use Wojciech\QuizGame\Domain\Game\Exception\CannotAddQuestionGameIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotJoinGameWhichIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotStartGame;
use Wojciech\QuizGame\Domain\Game\Exception\GameNotStarted;
use Wojciech\QuizGame\Domain\Game\Exception\PlayerIsNotSupposedThisGame;
use Wojciech\QuizGame\Domain\Player;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Domain\Score;

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
        $game = $this->createStartedGame();

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

    public function testCurrentQuestion_GameStarted_ReturnFirstAddedQuestion(): void
    {
        $expectedQuestion = $this->createQuestion();
        $game = Game::createNewGame();
        $game->join($this->createPlayer());
        $game->addQuestion($expectedQuestion);
        $game->addQuestion($this->createQuestion());
        $game->start();

        $currentQuestion = $game->currentQuestion();

        $this->assertSame($expectedQuestion, $currentQuestion);
    }

    public function testCurrentQuestion_GameNotStarted_ThrowsException(): void
    {
        $game = Game::createNewGame();

        $act = fn () => $game->currentQuestion();

        $expectedException = GameNotStarted::create();
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

    public function testScore_GameIsNotStarted_ThrowsException(): void
    {
        $game = Game::createNewGame();
        $answers = $this->createAnswers();
        $game->addQuestion($this->createQuestion($answers));
        $scoringPlayer = $this->createPlayer();
        $game->join($scoringPlayer);
        $givenAnswer = $answers[0];

        $act = fn () => $game->score($scoringPlayer, $givenAnswer);

        $expectedException = GameNotStarted::create();
        $this->assertException($expectedException, $act);
    }

    public function testScore_PlayerOutsideGame_ThrowsException(): void
    {
        $game = Game::createNewGame();
        $answers = $this->createAnswers();
        $game->addQuestion($this->createQuestion($answers));
        $joinedPlayer = $this->createPlayer();
        $game->join($joinedPlayer);
        $givenAnswer = $answers[0];
        $notJoinedPlayer = $this->createPlayer();
        $game->start();

        $act = fn () => $game->score($notJoinedPlayer, $givenAnswer);

        $expectedException = PlayerIsNotSupposedThisGame::create();
        $this->assertException($expectedException, $act);
    }

    public function testScore_AnswerNotForQuestion_ThrowsException(): void
    {
        $game = Game::createNewGame();
        $answers = $this->createAnswers();
        $game->addQuestion($this->createQuestion($answers));
        $scoringPlayer = $this->createPlayer();
        $game->join($scoringPlayer);
        $givenAnswer = Answer::createIncorrect('not for current question');
        $game->start();

        $act = fn () => $game->score($scoringPlayer, $givenAnswer);

        $expectedException = AnswerIsNotForCurrentQuestion::create();
        $this->assertException($expectedException, $act);
    }

    public function testScore_AlreadyAnswered_ThrowsException(): void
    {
        $game = Game::createNewGame();
        $givenAnswer = $this->createStub(Answer::class);
        $question = $this->createStub(Question::class);
        $question->method('answers')->willReturn(new ArrayCollection([$givenAnswer]));
        $question->method('equals')->willReturn(true);
        $scoringPlayer = $this->createStub(Player::class);
        $scoringPlayer->method('equals')->willReturn(true);
        $anotherPlayer = $this->createStub(Player::class);
        $game->addQuestion($question);
        $game->join($anotherPlayer);
        $game->join($scoringPlayer);
        $game->start();
        $game->score($scoringPlayer, $givenAnswer);

        $act = fn () => $game->score($scoringPlayer, $givenAnswer);

        $expectedException = AlreadyScored::create();
        $this->assertException($expectedException, $act);
    }

    public function testScore_QuestionNotAnswered_ScoreSubmitted(): void
    {
        $game = Game::createNewGame();
        $question = $this->createQuestion();
        $givenAnswer = $question->answers()[0];
        $scoringPlayer = $this->createPlayer();
        $game->addQuestion($question);
        $game->join($scoringPlayer);
        $game->start();

        $game->score($scoringPlayer, $givenAnswer);

        $expectedScore = new Score($game, $scoringPlayer, $question, $givenAnswer);
        $this->assertEquals($expectedScore, $game->scores()->toArray()[0]);
    }

    public function testScore_LastPlayerScoredLastQuestion_GameIsFinished(): void
    {
        $game = Game::createNewGame();
        $lastQuestion = $this->createQuestion();
        $lastAnswer = $lastQuestion->answers()[0];
        $lastPlayer = $this->createPlayer();
        $game->addQuestion($lastQuestion);
        $game->join($lastPlayer);
        $game->start();

        $game->score($lastPlayer, $lastAnswer);

        $expectedState = Game\State::FINISHED;
        $this->assertEquals($expectedState, $game->state());
    }

    public function testScore_AllPlayersAnsweredQuestion_CurrentQuestionChanged(): void
    {
        $game = Game::createNewGame();
        $firstQuestion = $this->createQuestion();
        $secondQuestion = $this->createQuestion();
        $answer = $firstQuestion->answers()[0];
        $player = $this->createPlayer();
        $game->addQuestion($firstQuestion);
        $game->addQuestion($secondQuestion);
        $game->join($player);
        $game->start();

        $game->score($player, $answer);

        $expectedQuestion = $secondQuestion;
        $this->assertSame($expectedQuestion, $game->currentQuestion());
    }

    public function testScore_NotAllPlayersAnswered_CurrentQuestionHasntChanged(): void
    {
        $game = Game::createNewGame();
        $firstQuestion = $this->createQuestion();
        $secondQuestion = $this->createQuestion();
        $answer = $firstQuestion->answers()[0];
        $player01 = $this->createPlayer();
        $player02 = $this->createPlayer();
        $game->addQuestion($firstQuestion);
        $game->addQuestion($secondQuestion);
        $game->join($player01);
        $game->join($player02);
        $game->start();

        $game->score($player01, $answer);

        $expectedQuestion = $firstQuestion;
        $this->assertSame($expectedQuestion, $game->currentQuestion());
    }

    protected function createAnswers(): array
    {
        return [Answer::createCorrect('1'), Answer::createIncorrect('1')];
    }

    protected function createQuestion(array $answers = null): Question
    {
        return new Question('q', ...($answers ?? $this->createAnswers()));
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
