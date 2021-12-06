<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Wojciech\QuizGame\Infrastructure\Controller\GameController;
use Wojciech\QuizGame\Infrastructure\Middleware\JsonApplicationMiddleware;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app
        ->group('/api', function (RouteCollectorProxy $group) {
            $group->post('/game/create', [GameController::class, 'createNewGame']);
        })
        ->addMiddleware(new JsonApplicationMiddleware());
};