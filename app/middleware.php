<?php
declare(strict_types=1);

use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Wojciech\QuizGame\Infrastructure\Middleware\SessionMiddleware;

return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->add(TwigMiddleware::create($app, $app->getContainer()->get(Twig::class)));
};