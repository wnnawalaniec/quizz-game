<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Question\Exception;

use JetBrains\PhpStorm\Pure;
use Wojciech\QuizGame\Domain\Exception\DomainException;

class RepeatingAnswersGiven extends DomainException
{
    #[Pure] public static function create(int $question): self
    {
        return new self(sprintf('Answers are repeating for question %d', $question));
    }
}