<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wojciech\QuizGame\Application\UserSession;

class SessionMiddleware implements MiddlewareInterface
{
    public function __construct(UserSession $session)
    {
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->session->isCreated()) {
            $this->session->start();
        }

        $request = $request->withAttribute('session', $_SESSION);
        return $handler->handle($request);
    }

    private UserSession $session;
}