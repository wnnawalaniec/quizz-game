<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Game;

use Wojciech\QuizGame\Domain\Game\Exception\GameIsFinished;

enum State: string
{
    case NEW_GAME = 'NEW_GAME';
    case STARTED = 'STARTED';
    case FINISHED = 'FINISHED';

    /**
     * @throws GameIsFinished
     */
    public function nextStage(): self
    {
        return match ($this)
        {
            self::NEW_GAME => self::STARTED,
            self::STARTED => self::FINISHED,
            self::FINISHED => throw GameIsFinished::createWithLastStage(self::FINISHED)
        };
    }
}