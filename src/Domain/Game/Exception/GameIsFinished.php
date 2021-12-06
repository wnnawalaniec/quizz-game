<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Game\Exception;

use JetBrains\PhpStorm\Pure;
use Wojciech\QuizGame\Domain\Exception\DomainException;
use Wojciech\QuizGame\Domain\Game\State;

class GameIsFinished extends DomainException
{
    #[Pure] public static function create(State $state): self
    {
        return new self(sprintf('Cannot go next stage. Stage %s is last', $state->value));
    }
}