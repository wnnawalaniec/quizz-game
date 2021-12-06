<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Application;

interface UserSession
{
    public function start(): void;

    public function destroy(): void;

    public function isCreated(): bool;

    public function store(string $key, mixed $value): void;

    public function has(string $key): bool;

    public function get(string $key): mixed;
}