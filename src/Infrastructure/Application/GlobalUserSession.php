<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Application;

use Wojciech\QuizGame\Application\UserSession;

class GlobalUserSession implements UserSession
{
    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function start(): void
    {
        if (!session_start()) {
            throw new \RuntimeException('Cannot start session');
        }
    }

    public function destroy(): void
    {
        if (!session_destroy()) {
            throw new \RuntimeException('Cannot destroy session');
        }
    }

    public function store(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            throw new \LogicException('Cannot obtain value of unknown key');
        }

        return $_SESSION[$key];
    }

    public function isCreated(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    private static self $instance;
}