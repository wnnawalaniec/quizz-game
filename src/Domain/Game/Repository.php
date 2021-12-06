<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain\Game;

use Wojciech\QuizGame\Domain\Game;

interface Repository
{
    public function store(Game $game): void;

    public function get(): ?Game;

    public function clear(): void;
}