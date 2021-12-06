<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Wojciech\QuizGame\Application\Service\Authentication;

class AuthenticationGuardMiddleware implements MiddlewareInterface
{
    public function __construct(Authentication $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->authenticationService->isAuthenticated()) {
            return (new Response())->withStatus(401);
        }

        return $handler->handle($request);
    }

    private Authentication $authenticationService;
}