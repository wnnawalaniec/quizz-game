<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Views\Twig;
use Wojciech\QuizGame\Application\Service\Persistence;
use Wojciech\QuizGame\Application\UserSession;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Game\Exception\CannotJoinGameWhichIsNotNew;
use Wojciech\QuizGame\Domain\Player;
use Wojciech\QuizGame\Domain\Service\Exception\NoGameExists;
use Wojciech\QuizGame\Domain\Service\GameService;

class JoinController
{
    public function __construct(GameService $gameService, Persistence $persistence, UserSession $session)
    {
        $this->gameService = $gameService;
        $this->persistence = $persistence;
        $this->session = $session;
    }

    public function joinView(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->session->isCreated()) {
            throw new RuntimeException('This endpoint requires session');
        }

        $game = $this->gameService->game();
        if ($this->isInTheGame($game)) {
            return $this->redirectToGame($response);
        }

        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'join.html.twig'
        );
    }

    public function join(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->session->isCreated()) {
                throw new RuntimeException('This endpoint requires session');
            }

            $game = $this->gameService->game();
            if ($this->isInTheGame($game)) {
                return $this->redirectToGame($response);
            }

            if ($game->state() !== Game\State::NEW_GAME) {
                match ($game->state()) {
                    Game\State::STARTED => $msg = "Nie można dołączyć do gry, gdyż ta została już rozpoczęta.",
                    Game\State::FINISHED => $msg = "Nie można dołączyć do gry, gdyż ta została już zakończona."
                };
                return $this->renderDisabled($request, $response, $msg);
            }

            $player = new Player($request->getParsedBody()['name']);
            $this->gameService->join($player);
            $this->persistence->flush();
            $this->session->store('player', [
                'id' => $player->id(),
                'game' => $game->id()
            ]);
            return $this->redirectToGame($response);
        } catch (CannotJoinGameWhichIsNotNew|NoGameExists) {
            return $this->renderError(
                $request,
                $response,
                'Nie można dołączyć do gry ponieważ została ona zakończona bądź jest w trakcie.'
            );
        }
    }

    private function redirectToGame(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('Location', '/game')
            ->withStatus(302);
    }

    protected function isInTheGame(Game $game): bool
    {
        return $this->session->has('player')
            && $this->session->get('player')['game'] === $game->id();
    }

    protected function renderError(RequestInterface $request, ResponseInterface $response, string $error): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'join.html.twig',
            ['error' => $error]
        );
    }

    protected function renderDisabled(RequestInterface $request, ResponseInterface $response, string $error): ResponseInterface
    {
        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'join.html.twig',
            ['disabled' => true, 'message' => $error]
        );
    }

    private GameService $gameService;
    private Persistence $persistence;
    private UserSession $session;
}