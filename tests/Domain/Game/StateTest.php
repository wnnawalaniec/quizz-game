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

    public function testGoingToNextStage_GameStarted_ThrowsException(): void
    {
        $state = State::STARTED;

        $act = fn () => $state->nextStage();

        $expectedException = GameIsFinished::create($state);
        $this->assertException($expectedException, $act);
    }
}
