<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginRedirectMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response->getStatusCode() === 401) {
            return $response->withHeader('Location', '/admin/login?redirect=' . $request->getUri()->getPath())
                ->withStatus(302);
        }

        return $response;
    }
}