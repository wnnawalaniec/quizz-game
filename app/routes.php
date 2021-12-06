<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Wojciech\QuizGame\Infrastructure\Controller\GameController;
use Wojciech\QuizGame\Infrastructure\Controller\LoginController;
use Wojciech\QuizGame\Infrastructure\Middleware\AuthenticationGuardMiddleware;
use Wojciech\QuizGame\Infrastructure\Middleware\JsonApplicationMiddleware;
use Wojciech\QuizGame\Infrastructure\Middleware\NoCacheMiddleware;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app
        ->get('/login', [LoginController::class, 'login'])
        ->addMiddleware($app->getContainer()->get(NoCacheMiddleware::class));

    $app
        ->get('/logout', [LoginController::class, 'logout'])
        ->addMiddleware($app->getContainer()->get(NoCacheMiddleware::class));

    $app
        ->group('/api', function (RouteCollectorProxy $group) {
            $group->post('/game/create', [GameController::class, 'createNewGame']);
            $group->get('/game', [GameController::class, 'game']);
            $group->post('/questions', [GameController::class, 'addQuestion']);
            $group->get('/questions', [GameController::class, 'listQuestions']);
        })
        ->addMiddleware($app->getContainer()->get(JsonApplicationMiddleware::class))
        ->addMiddleware($app->getContainer()->get(AuthenticationGuardMiddleware::class));
};