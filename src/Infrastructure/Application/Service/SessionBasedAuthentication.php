<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Application\Service;

use Wojciech\QuizGame\Application\Exception\InvalidCredentials;
use Wojciech\QuizGame\Application\Service\Authentication;
use Wojciech\QuizGame\Application\Settings;
use Wojciech\QuizGame\Application\UserSession;

class SessionBasedAuthentication implements Authentication
{
    protected function __construct(Settings $settings, UserSession $session) {
        $this->settings = $settings;
        $this->session = $session;
    }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(Settings $settings, UserSession $session): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($settings, $session);
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
        if (!$this->session->isCreated()) {
            throw new \RuntimeException('Session not started');
        }

        if (
            $user === $this->settings->get('username')
            && $password === $this->settings->get('password')
        ) {
            $this->session->store('authenticated', true);
        }

        throw InvalidCredentials::create();
    }

    private Settings $settings;
    private UserSession $session;
    private static self $instance;
}