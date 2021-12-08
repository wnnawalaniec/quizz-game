<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Player;

use Wojciech\QuizGame\Domain\Player;

interface Repository
{
    public function get(string $id): ?Player;
}