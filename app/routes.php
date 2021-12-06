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
use Wojciech\QuizGame\Infrastructure\Middleware\SessionMiddleware;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app
        ->get('/admin/login', [LoginController::class, 'login'])
        ->addMiddleware($app->getContainer()->get(NoCacheMiddleware::class));

    $app
        ->get('/admin/logout', [LoginController::class, 'logout'])
        ->addMiddleware($app->getContainer()->get(NoCacheMiddleware::class));

    $app
        ->group('/admin/api', function (RouteCollectorProxy $group) {
            $group->get('/game', [GameController::class, 'game']);
            $group->post('/game/create', [GameController::class, 'createNewGame']);
            $group->post('/game/start', [GameController::class, 'startGame']);
            $group->post('/questions', [GameController::class, 'addQuestion']);
            $group->get('/questions', [GameController::class, 'listQuestions']);
        })
        ->addMiddleware($app->getContainer()->get(JsonApplicationMiddleware::class))
        ->addMiddleware($app->getContainer()->get(AuthenticationGuardMiddleware::class));

    $app
        ->group('/api', function (RouteCollectorProxy $group) {
            $group->post('/api/game/join', [GameController::class, 'joinGame']);
        })
        ->addMiddleware($app->getContainer()->get(JsonApplicationMiddleware::class))
        ->addMiddleware($app->getContainer()->get(SessionMiddleware::class));

    $app
        ->get('/status', [GameController::class, 'status'])
        ->addMiddleware($app->getContainer()->get(JsonApplicationMiddleware::class));
};