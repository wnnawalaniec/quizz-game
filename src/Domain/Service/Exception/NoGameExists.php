<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Service\Exception;

use JetBrains\PhpStorm\Pure;
use Wojciech\QuizGame\Domain\Exception\DomainException;

class NoGameExists extends DomainException
{
    #[Pure] public static function create(): self
    {
        return new self('Game not created yet.');
    }
}