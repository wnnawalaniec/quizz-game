<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Wojciech\QuizGame\Application\Transaction;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Game\Repository;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Domain\Service\Exception\CannotAddQuestionGameNotStartedYet;
use Wojciech\QuizGame\Domain\Service\Exception\CannotStartNewGameWhenThereIsAlreadyOne;
use Wojciech\QuizGame\Domain\Service\GameService;
use function json_decode;
use function json_encode;

class GameController
{
    public function __construct(
        GameService $gameService,
        Transaction $transaction,
        Repository $repository
    ){
        $this->gameService = $gameService;
        $this->transaction = $transaction;
        $this->repository = $repository;
    }

    public function createNewGame(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $this->gameService->startNewGame();
        } catch (CannotStartNewGameWhenThereIsAlreadyOne $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'GAME_ALREADY_EXISTS']));
            return $response;
        }
        $this->transaction->flush();
        return $response->withStatus(204);
    }

    public function addQuestion(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $possibleAnswers = [];
        foreach ($data['answers'] as $answer) {
            $possibleAnswers[] = new Answer($answer['text'], $answer['is_correct']);
        }
        try {
            $this->gameService->addQuestion(new Question($data['text'], $possibleAnswers));
        } catch (Question\Exception\EmptyTextGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'EMPTY_QUESTION']));
            return $response;
        } catch (Question\Exception\NoAnswerGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'NO_ANSWER_GIVEN']));
            return $response;
        } catch (Question\Exception\NoCorrectAnswerGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'NO_CORRECT_ANSWER_GIVEN']));
            return $response;
        } catch (Question\Exception\TooManyCorrectAnswersGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'MORE_THAN_ONE_CORRECT_ANSWER']));
            return $response;
        } catch (CannotAddQuestionGameNotStartedYet $e) {
            $response = $response->withStatus(409);
            $response->getBody()->write(json_encode(['error' => 'GAME_NOT_STARTED_YET']));
            return $response;
        }
        $this->transaction->flush();
        return $response->withStatus(204);
    }

    public function listQuestions(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $game = $this->repository->get();

        $questions = [];
        if ($game !== null) {
            $questions = $game->questions();
        }
        $response->getBody()->write(json_encode($questions->toArray()));
        return $response;
    }

    public function game(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $game = $this->repository->get();

        if ($game === null) {
            $response->withStatus(404);
            return $response;
        }

        $response->getBody()->write(json_encode($game));
        return $response;
    }
    private Repository $repository;
    private GameService $gameService;
    private Transaction $transaction;
}