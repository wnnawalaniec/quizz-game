<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Game\Exception;

use JetBrains\PhpStorm\Pure;
use Wojciech\QuizGame\Domain\Exception\DomainException;

class CannotStartGame extends DomainException
{
    #[Pure] public static function createForNoQuestions(): self
    {
        return new self('Cannot start game because it has no questions');
    }

    #[Pure] public static function createForNoPlayers(): self
    {
        return new self('Cannot start game because no players has joined');
    }
}