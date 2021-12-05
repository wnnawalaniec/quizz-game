<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Question\Exception;

use JetBrains\PhpStorm\Pure;
use Wojciech\QuizGame\Domain\Exception\DomainException;

class EmptyTextGiven extends DomainException
{
    #[Pure] public static function create(): self
    {
        return new self('Question must have some text');
    }
}