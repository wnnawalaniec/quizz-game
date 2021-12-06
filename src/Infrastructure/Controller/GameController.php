<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Wojciech\QuizGame\Application\Transaction;
use Wojciech\QuizGame\Domain\Service\Exception\CannotStartNewGameWhenThereIsAlreadyOne;
use Wojciech\QuizGame\Domain\Service\GameService;

class GameController
{
    public function __construct(GameService $gameService, Transaction $transaction)
    {
        $this->gameService = $gameService;
        $this->transaction = $transaction;
    }

    public function createNewGame(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getAttribute('session') === null) {
            return $response->withStatus(401);
        }

        try {
            $this->gameService->startNewGame();
        } catch (CannotStartNewGameWhenThereIsAlreadyOne $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(\json_encode(['error' => 'GAME_ALREADY_EXISTS']));
            return $response;
        }
        $this->transaction->flush();
        return $response->withStatus(204);
    }

    private GameService $gameService;
    private Transaction $transaction;
}