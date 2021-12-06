<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Wojciech\QuizGame\Application\Exception\InvalidCredentials;
use Wojciech\QuizGame\Application\Service\Authentication;
use Wojciech\QuizGame\Application\Settings;

class LoginController
{
    public function __construct(Settings $settings, Authentication $authenticationService)
    {
        $this->settings = $settings;
        $this->authenticationService = $authenticationService;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($this->authenticationService->isAuthenticated()) {
            return $response;
        }

        if ($this->areCredentialsSupplied($request)) {
            try {
                $this
                    ->authenticationService
                    ->authenticate(
                        $request->getServerParams()['PHP_AUTH_USER'],
                        $request->getServerParams()['PHP_AUTH_PW']
                    );
                return $response;
            } catch (InvalidCredentials $e) {
                $response = $response->withStatus(401);
            }
        }

        return $response->withHeader('WWW-Authenticate', 'Basic');
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $_SESSION = [];
        session_destroy();
        return $response;
    }

    private function areCredentialsSupplied(ServerRequestInterface $request): bool
    {
        return isset($request->getServerParams()['PHP_AUTH_USER'])
            && isset($request->getServerParams()['PHP_AUTH_PW']);
    }

    private Authentication $authenticationService;
}