<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Game\Exception;

use JetBrains\PhpStorm\Pure;
use Wojciech\QuizGame\Domain\Exception\DomainException;

class PlayerIsNotSupposedThisGame extends DomainException
{
    #[Pure] public static function create(): self
    {
        return new self('Player must join the game to score');
    }
}