<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Views\Twig;
use Wojciech\QuizGame\Application\Service\Persistence;
use Wojciech\QuizGame\Application\UserSession;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Game\Exception\AlreadyScored;
use Wojciech\QuizGame\Domain\Game\Exception\AnswerIsNotForCurrentQuestion;
use Wojciech\QuizGame\Domain\Game\Exception\CannotAddQuestionGameIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotJoinGameWhichIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotStartGame;
use Wojciech\QuizGame\Domain\Game\Exception\GameIsFinished;
use Wojciech\QuizGame\Domain\Game\Exception\GameNotStarted;
use Wojciech\QuizGame\Domain\Game\Exception\PlayerIsNotSupposedThisGame;
use Wojciech\QuizGame\Domain\Player;
use Wojciech\QuizGame\Domain\Player\Repository;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Domain\Service\Exception\CannotStartNewGameWhenThereIsAlreadyOne;
use Wojciech\QuizGame\Domain\Service\Exception\NoGameExists;
use Wojciech\QuizGame\Domain\Service\GameService;
use function json_decode;
use function json_encode;

class GameController
{
    public function __construct(
        GameService $gameService,
        Persistence $transaction,
        UserSession $session,
        Repository $repository
    ){
        $this->gameService = $gameService;
        $this->persistence = $transaction;
        $this->session = $session;
        $this->repository = $repository;
    }

    public function createNewGame(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $game = $this->gameService->createNew();
        } catch (CannotStartNewGameWhenThereIsAlreadyOne $e) {
            $errors =[
                'Nie można uruchomić nowej gry gdy już jedna działa.'
            ];
        }
        $this->persistence->flush();

        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'admin.html.twig',
            [
                'game' => $game ?? null,
                'errors' => $errors ?? []
            ]
        );
    }

    public function addQuestion(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $this->gameService->addQuestion($this->questionFromJson($request));
        } catch (Question\Exception\EmptyTextGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'QUESTION_MUST_HAVE_TEXT']));
            return $response;
        } catch (Question\Exception\NoAnswerGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'QUESTION_MUST_HAVE_AT_LEAST_2_POSSIBLE_ANSWERS']));
            return $response;
        } catch (Question\Exception\NoCorrectAnswerGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'QUESTION_MUST_HAVE_AT_LEAST_1_CORRECT_ANSWER']));
            return $response;
        } catch (Question\Exception\TooManyCorrectAnswersGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'QUESTION_MUST_HAVE_ONLY_1_CORRECT_ANSWER']));
            return $response;
        } catch (NoGameExists $e) {
            $response = $response->withStatus(409);
            $response->getBody()->write(json_encode(['error' => 'NO_GAME_EXISTS']));
            return $response;
        } catch (Question\Exception\OnlyOneAnswerGiven $e) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'QUESTION_MUST_HAVE_MORE_THAN_ONE_POSSIBLE_ANSWER']));
            return $response;
        } catch (CannotAddQuestionGameIsNotNew $e) {
            $response = $response->withStatus(409);
            $response->getBody()->write(json_encode(['error' => 'QUESTIONS_MAY_BE_ADDED_ONLY_TO_NEW_GAME']));
            return $response;
        }
        $this->persistence->flush();
        return $response->withStatus(204);
    }

    public function listQuestions(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $questions = $this->gameService->questions()->toArray();
        } catch (NoGameExists $e) {
            $questions = [];
        }
        $response->getBody()->write(json_encode($questions));
        return $response;
    }

    public function game(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $game = $this->gameService->game();
        } catch (NoGameExists $e) {
            $game = null;
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin.html.twig', ['game'=>$game]);
    }

    public function startGame(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $this->gameService->start();
            $this->persistence->flush();
            return $response->withStatus(200);
        } catch (CannotStartGame $e) {
            $response->withStatus(409);
            $response->getBody()->write(json_encode(['error' => 'CANNOT_START_GAME_NO_QUESTIONS_OR_PLAYERS']));
            return $response;
        } catch (GameIsFinished $e) {
            $response->withStatus(409);
            $response->getBody()->write(json_encode(['error' => 'CANNOT_START_FINISHED_GAME']));
            return $response;
        } catch (NoGameExists $e) {
            $response->withStatus(409);
            $response->getBody()->write(json_encode(['error' => 'NO_GAME_EXISTS']));
            return $response;
        }
    }

    public function joinGame(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->session->isCreated()) {
                throw new RuntimeException('This endpoint requires session');
            }

            $game = $this->gameService->game();
            if (
                $this->session->has('player')
                && $this->session->get('player')['game'] === $game->id()
            ) {
                $response->getBody()->write(json_encode(['error' => 'ALREADY_JOINED']));
                return $response->withStatus(400);
            }

            $player = $this->playerFromJson($request);
            $this->gameService->join($player);
            $this->persistence->flush();
            $this->session->store('player', [
                'id' => $player->id(),
                'game' => $game->id()
            ]);
            $response->getBody()->write(json_encode($player));
            return $response->withStatus(201);
        } catch (CannotJoinGameWhichIsNotNew $e) {
            $response->withStatus(409);
            $response->getBody()->write(json_encode(['error' => 'CANNOT_JOIN_GAME_WHICH_IS_NOT_NEW']));
            return $response;
        } catch (NoGameExists $e) {
            $response->withStatus(409);
            $response->getBody()->write(json_encode(['error' => 'NO_GAME_EXISTS']));
            return $response;
        }
    }

    public function score(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->session->isCreated()) {
            throw new RuntimeException('This endpoint requires session');
        }

        if (!$this->session->has('player')) {
            $response->getBody()->write(json_encode(['error' => 'ACCESS_DENIED']));
            return $response->withStatus(403);
        }

        $game = $this->gameService->game();
        $player = $this->repository->get($this->session->get('player')['id']);

        if (is_null($player) || $this->session->get('player')['game'] !== $game->id()) {
            $response->getBody()->write(json_encode(['error' => 'ACCESS_DENIED']));
            return $response->withStatus(403);
        }

        $data = json_decode($request->getBody()->getContents(), true);
        $answer = $game
            ->currentQuestion()
            ->answers()
            ->filter(fn (Answer $a) => $a->id() === $data['answer'])
            ->first();

        if ($answer === false) {
            $response->getBody()->write(json_encode(['error' => 'QUESTION_CANNOT_BE_ANSWERED_WITH_IT']));
            return $response->withStatus(400);
        }

        try {
            $game->score($player, $answer);
            $this->persistence->flush();
            return $response->withStatus(201);
        } catch (AlreadyScored $e) {
            $response->getBody()->write(json_encode(['error' => 'ALREADY_SCORED']));
            return $response->withStatus(400);
        } catch (AnswerIsNotForCurrentQuestion $e) {
            $response->getBody()->write(json_encode(['error' => 'QUESTION_CANNOT_BE_ANSWERED_WITH_IT']));
            return $response->withStatus(400);
        } catch (GameNotStarted $e) {
            $response->getBody()->write(json_encode(['error' => 'GAME_NOT_STARTED']));
            return $response->withStatus(409);
        } catch (PlayerIsNotSupposedThisGame $e) {
            $response->getBody()->write(json_encode(['error' => 'ACCESS_DENIED']));
            return $response->withStatus(401);
        } catch (GameIsFinished $e) {
            $response->getBody()->write(json_encode(['error' => 'GAME_IS_FINISHED']));
            return $response->withStatus(409);
        }
    }

    public function status(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $game = $this->gameService->game();
            $status = [
                'status' => $game->state(),
                'question' => $game->currentQuestion()->jsonSerialize()
            ];
        } catch (NoGameExists|GameNotStarted $e) {
            $status = [
                'status' => 'GAME_NOT_STARTED',
                'question' => null,
                'answers' => []
            ];
        } catch (GameIsFinished $e) {
            $status = [
                'status' => 'GAME_FINISHED',
                'results' => $game->results()
            ];
        }

        $response->getBody()->write(json_encode($status));
        return $response;
    }

    protected function playerFromJson(RequestInterface $request): Player
    {
        $data = json_decode($request->getBody()->getContents(), true);
        return new Player($data['name']);
    }

    /**
     * @throws Question\Exception\OnlyOneAnswerGiven
     * @throws Question\Exception\EmptyTextGiven
     * @throws Question\Exception\TooManyCorrectAnswersGiven
     * @throws Question\Exception\NoAnswerGiven
     * @throws Question\Exception\NoCorrectAnswerGiven
     */
    protected function questionFromJson(ServerRequestInterface $request): Question
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $possibleAnswers = [];
        foreach ($data['answers'] as $answer) {
            $possibleAnswers[] = new Answer($answer['text'], $answer['is_correct']);
        }
        return new Question($data['text'], ...$possibleAnswers);
    }

    private GameService $gameService;
    private Persistence $persistence;
    private UserSession $session;
    private Repository $repository;
}