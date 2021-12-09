<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Wojciech\QuizGame\Infrastructure\Controller\AdminController;
use Wojciech\QuizGame\Infrastructure\Controller\GameController;
use Wojciech\QuizGame\Infrastructure\Controller\JoinController;
use Wojciech\QuizGame\Infrastructure\Controller\LoginController;
use Wojciech\QuizGame\Infrastructure\Middleware\AuthenticationGuardMiddleware;
use Wojciech\QuizGame\Infrastructure\Middleware\JsonApplicationMiddleware;
use Wojciech\QuizGame\Infrastructure\Middleware\LoginRedirectMiddleware;
use Wojciech\QuizGame\Infrastructure\Middleware\NoCacheMiddleware;
use Wojciech\QuizGame\Infrastructure\Middleware\SessionMiddleware;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        return $response->withHeader('Location', '/join')
            ->withStatus(302);
    });

    $app->get('/join', [JoinController::class, 'joinView']);
    $app->post('/join', [JoinController::class, 'join']);

    $app
        ->group('/game', function (RouteCollectorProxy $group) {
            $group->get('', [GameController::class, 'view']);
            $group->post('/score', [GameController::class, 'score']);
            $group->get('/finish', [GameController::class, 'finished']);
        })
        ->addMiddleware($app->getContainer()->get(SessionMiddleware::class));

    $app
        ->get('/admin/login', [LoginController::class, 'login'])
        ->addMiddleware($app->getContainer()->get(NoCacheMiddleware::class));
    $app
        ->get('/admin/logout', [LoginController::class, 'logout'])
        ->addMiddleware($app->getContainer()->get(NoCacheMiddleware::class));

    $app
        ->group('/admin/api', function (RouteCollectorProxy $group) {
            $group->get('/game', [AdminController::class, 'game']);
            $group->post('/game/create', [AdminController::class, 'createNewGame']);
            $group->post('/game/start', [AdminController::class, 'startGame']);
            $group->post('/questions', [AdminController::class, 'addQuestion']);
            $group->get('/questions', [AdminController::class, 'listQuestions']);
        })
        ->addMiddleware($app->getContainer()->get(JsonApplicationMiddleware::class))
        ->addMiddleware($app->getContainer()->get(AuthenticationGuardMiddleware::class));

    $app->get('/admin', [AdminController::class, 'panel'])
        ->addMiddleware($app->getContainer()->get(AuthenticationGuardMiddleware::class))
        ->addMiddleware($app->getContainer()->get(LoginRedirectMiddleware::class));

    $app->post('/admin/game/start', [AdminController::class, 'start'])
        ->addMiddleware($app->getContainer()->get(AuthenticationGuardMiddleware::class))
        ->addMiddleware($app->getContainer()->get(LoginRedirectMiddleware::class));

    $app
        ->get('/api/status', [GameController::class, 'status'])
        ->addMiddleware($app->getContainer()->get(JsonApplicationMiddleware::class))
        ->addMiddleware($app->getContainer()->get(LoginRedirectMiddleware::class));
};