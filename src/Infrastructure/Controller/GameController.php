<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Views\Twig;
use Wojciech\QuizGame\Application\Service\Persistence;
use Wojciech\QuizGame\Application\UserSession;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Game\Exception\AlreadyScored;
use Wojciech\QuizGame\Domain\Game\Exception\AnswerIsNotForCurrentQuestion;
use Wojciech\QuizGame\Domain\Game\Exception\GameIsFinished;
use Wojciech\QuizGame\Domain\Game\Exception\GameNotStarted;
use Wojciech\QuizGame\Domain\Game\Exception\PlayerIsNotSupposedThisGame;
use Wojciech\QuizGame\Domain\Game\State;
use Wojciech\QuizGame\Domain\Player\Repository;
use Wojciech\QuizGame\Domain\Service\Exception\NoGameExists;
use Wojciech\QuizGame\Domain\Service\GameService;

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

    public function view(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->session->isCreated()) {
            throw new RuntimeException('This endpoint requires session');
        }

        try {
            $game = $this->gameService->game();
        } catch (NoGameExists $e) {
            return $this->redirectToJoinGame($response);
        }

        if (!$this->isInTheGame($game)) {
            $this->session->destroy();
            return $this->redirectToJoinGame($response);
        }

        if ($game->state() === State::FINISHED) {
            return $this->redirectToFinish($response);
        }

        $player = $this->repository->get($this->session->get('player')['id']);

        if (is_null($player) || !$this->isPlayerFromGame($game)) {
            $this->session->destroy();
            return $this->redirectToJoinGame($response);
        }

        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'game.html.twig',
            [
                'hasStarted' => $game->state() !== State::NEW_GAME,
                'question' => $game->state() === State::STARTED ? $game->currentQuestion()->jsonSerialize() : null,
                'hasAnswered' => $player ? $game->hasAnsweredCurrentQuestion($player) : false
            ]
        );
    }

    public function score(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->session->isCreated()) {
            throw new RuntimeException('This endpoint requires session');
        }

        try {
            $game = $this->gameService->game();
        } catch (NoGameExists $e) {
            return $this->redirectToJoinGame($response);
        }

        if (!$this->isInTheGame($game)) {
            $this->session->destroy();
            return $this->redirectToJoinGame($response);
        }

        $player = $this->repository->get($this->session->get('player')['id']);

        if (is_null($player) || !$this->isPlayerFromGame($game)) {
            $this->session->destroy();
            return $this->redirectToJoinGame($response);
        }

        $data = $request->getParsedBody();
        $answer = $game
            ->currentQuestion()
            ->answers()
            ->filter(fn (Answer $a) => $a->id() === (int) $data['answer'])
            ->first();

        if ($answer === false) {
            return $this->renderError($request, $response, $game, 'Wystąpił błąd. Nie ma takiej odpowiedzi.');
        }

        try {
            $game->score($player, $answer);
            $this->persistence->flush();
            return $this->redirectToGame($response);
        } catch (AlreadyScored $e) {
            return $this->redirectToGame($response);
        } catch (AnswerIsNotForCurrentQuestion $e) {
            return $this->renderError($request, $response, $game, 'Wystąpił błąd. Nie ma takiej odpowiedzi.');
        } catch (GameNotStarted|PlayerIsNotSupposedThisGame) {
            return $this->redirectToJoinGame($response);
        } catch (GameIsFinished) {
            return $this->redirectToFinish($response);
        }
    }

    public function finished(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->session->isCreated()) {
            throw new RuntimeException('This endpoint requires session');
        }

        try {
            $game = $this->gameService->game();
        } catch (NoGameExists $e) {
            return $this->redirectToJoinGame($response);
        }

        if (!$this->isInTheGame($game)) {
            $this->session->destroy();
            return $this->redirectToJoinGame($response);
        }

        $player = $this->repository->get($this->session->get('player')['id']);

        if (is_null($player) || !$this->isPlayerFromGame($game)) {
            $this->session->destroy();
            return $this->redirectToJoinGame($response);
        }

        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'finished.html.twig',
            [
                'hasWon' => $game->hasWon($player),
                'score' => $game->playerScore($player),
            ]
        );
    }

    public function watch(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        try {
            $game = $this->gameService->game();
        } catch (NoGameExists $e) {
            return $view->render($response, 'watch.html.twig');
        }

        return $view->render(
            $response,
            'watch.html.twig',
            [
                'game' => $game->jsonSerialize()
            ]
        );
    }

    protected function isInTheGame(Game $game): bool
    {
        return $this->session->has('player')
            && $this->session->get('player')['game'] === $game->id();
    }

    protected function redirectToJoinGame(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('Location', '/join')
            ->withStatus(302);
    }

    protected function redirectToFinish(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('Location', '/game/finish')
            ->withStatus(302);
    }

    protected function redirectToGame(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('Location', '/game')
            ->withStatus(302);
    }

    protected function isPlayerFromGame(Game $game): bool
    {
        return $this->session->get('player')['game'] === $game->id();
    }

    private function renderError(RequestInterface $request, ResponseInterface $response, Game $game, string $error): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'game.html.twig',
            [
                'hasStarted' => true,
                'question' => $game->currentQuestion()->jsonSerialize(),
                'error' => $error
            ]
        );
    }

    private GameService $gameService;
    private Persistence $persistence;
    private UserSession $session;
    private Repository $repository;
}