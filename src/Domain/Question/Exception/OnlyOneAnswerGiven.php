<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Question\Exception;

use JetBrains\PhpStorm\Pure;
use Wojciech\QuizGame\Domain\Exception\DomainException;

class OnlyOneAnswerGiven extends DomainException
{
    #[Pure] public static function create(): self
    {
        return new self('Cannot create question with only one answer');
    }
}