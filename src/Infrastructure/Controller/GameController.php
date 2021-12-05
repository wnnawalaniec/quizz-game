<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wojciech\QuizGame\Domain\Game\Repository;
use Wojciech\QuizGame\Infrastructure\Domain\Question\Loader;

class GameController
{
    public function createNewGame(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
    }

    private Repository $repository;
    private Loader $loader;
}