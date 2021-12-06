<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Application;

use Wojciech\QuizGame\Application\AuthenticationService;
use Wojciech\QuizGame\Application\Exception\InvalidCredentials;
use Wojciech\QuizGame\Application\Settings;

class SessionBasedAuthenticationService implements AuthenticationService
{
    private static SessionBasedAuthenticationService $instance;

    protected function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(Settings $settings): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($settings);
        }

        return self::$instance;
    }

    public function isAuthenticated(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        return $_SESSION['authenticated'] ?? false === true;
    }

    public function authenticate(string $user, string $password): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session not started');
        }

        if (
            $user === $this->settings->get('username')
            && $password === $this->settings->get('password')
        ) {
            $_SESSION['authenticated'] = true;
        }

        throw InvalidCredentials::create();
    }

    private Settings $settings;
}