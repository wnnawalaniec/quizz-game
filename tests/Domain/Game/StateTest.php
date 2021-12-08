<?php
declare(strict_types=1);

namespace Tests\Wojciech\QuizGame\Domain\Game;

use Tests\Wojciech\QuizGame\BaseTest;
use Wojciech\QuizGame\Domain\Game\Exception\GameIsFinished;
use Wojciech\QuizGame\Domain\Game\State;

class StateTest extends BaseTest
{
    public function testGoingToNextStage_NewGame_GameStarted(): void
    {
        $state = State::NEW_GAME;

        $nextState = $state->nextStage();

        $this->assertEquals(State::STARTED, $nextState);
    }

    public function testGoingToNextStage_GameStarted_GameFinished(): void
    {
        $state = State::STARTED;

        $nextState = $state->nextStage();

        $this->assertEquals(State::FINISHED, $nextState);
    }

    public function testGoingToNextStage_GameFinished_ThrowsException(): void
    {
        $state = State::FINISHED;

        $act = fn () => $state->nextStage();

        $expectedException = GameIsFinished::createWithLastStage($state);
        $this->assertException($expectedException, $act);
    }
}
