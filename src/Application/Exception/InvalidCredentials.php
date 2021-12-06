<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Application\Exception;

use JetBrains\PhpStorm\Pure;

class InvalidCredentials extends ApplicationException
{
    #[Pure] public static function create(): self
    {
        return new self('Invalid credentials');
    }
}