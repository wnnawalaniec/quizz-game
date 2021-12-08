<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Game\Exception;

use JetBrains\PhpStorm\Pure;
use Wojciech\QuizGame\Domain\Exception\DomainException;
use Wojciech\QuizGame\Domain\Game\State;

class GameIsFinished extends DomainException
{
    #[Pure] public static function createWithLastStage(State $lastStage): self
    {
        return new self(sprintf('Cannot go next stage. Stage %s is last', $lastStage->value));
    }

    public static function create(): self
    {
        return new self('Game finished');
    }
}