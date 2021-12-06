<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Application\Service;

use Wojciech\QuizGame\Application\Exception\InvalidCredentials;

interface Authentication
{
    public function isAuthenticated(): bool;

    /** @throws InvalidCredentials */
    public function authenticate(string $user, string $password): void;
}