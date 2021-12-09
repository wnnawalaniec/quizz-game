<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Wojciech\QuizGame\Application\Service\Persistence;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Game\Exception\CannotAddQuestionGameIsNotNew;
use Wojciech\QuizGame\Domain\Game\Exception\CannotStartGame;
use Wojciech\QuizGame\Domain\Game\Exception\GameIsFinished;
use Wojciech\QuizGame\Domain\Game\Exception\GameNotStarted;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Domain\Service\Exception\CannotStartNewGameWhenThereIsAlreadyOne;
use Wojciech\QuizGame\Domain\Service\Exception\NoGameExists;
use Wojciech\QuizGame\Domain\Service\GameService;

class AdminController
{
    public function __construct(
        GameService $gameService,
        Persistence $transaction
    ){
        $this->gameService = $gameService;
        $this->persistence = $transaction;
    }

    public function panel(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $game = $this->gameService->game();
        } catch (NoGameExists $e) {
            $game = null;
        }

        return $this->renderPanel($request, $response, $game);
    }

    public function start(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $game = $this->gameService->game();
        } catch (NoGameExists $e) {
            return $this->renderPanel($request, $response, null, 'Nie ma jeszcze żadnej gry');
        }

        if ($game->state() !== Game\State::NEW_GAME) {
            return $this->renderPanel($request, $response, $game, 'Gra już jest rozpoczęta bądź zakończona');
        }

        try {
            $game->start();
            $this->persistence->flush();
        } catch (CannotStartGame|GameIsFinished) {
            return $this->renderPanel($request, $response, $game, 'Gra już jest rozpoczęta bądź zakończona');
        }

        return $this->renderPanel($request, $response, $game);
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
        return $view->render($response, 'admin.html.twig', ['game' => $game]);
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

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function renderPanel(
        RequestInterface $request,
        ResponseInterface $response,
        ?Game $game,
        ?string $error = null
    ): ResponseInterface {
        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'admin.html.twig',
            [
                'game' => $game?->jsonSerialize(),
                'error' => $error
            ]
        );
    }

    private GameService $gameService;
    private Persistence $persistence;
}