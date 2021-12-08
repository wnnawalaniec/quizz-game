<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Wojciech\QuizGame\Application\Service\Persistence;
use Wojciech\QuizGame\Application\UserSession;
use Wojciech\QuizGame\Domain\Game;
use Wojciech\QuizGame\Domain\Player\Repository;
use Wojciech\QuizGame\Domain\Service\Exception\CannotStartNewGameWhenThereIsAlreadyOne;
use Wojciech\QuizGame\Domain\Service\Exception\NoGameExists;
use Wojciech\QuizGame\Domain\Service\GameService;

class AdminController
{
    /**
     * @param GameService $gameService
     * @param Persistence $persistence
     * @param UserSession $session
     * @param Repository $repository
     */
    public function __construct(
        GameService $gameService,
        Persistence $persistence,
        UserSession $session,
        Repository $repository)
    {
        $this->gameService = $gameService;
        $this->persistence = $persistence;
        $this->session = $session;
        $this->repository = $repository;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function panel(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $game = $this->gameService->game();
        } catch (NoGameExists $e) {
            $game = null;
        }

        return $this->renderPanel($request, $response, $game);
    }

    public function createGame(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $game = $this->gameService->createNew();
            $this->persistence->flush();
        } catch (CannotStartNewGameWhenThereIsAlreadyOne $e) {
            $game = $this->gameService->game();
        }

        return $this->renderPanel($request, $response, $game);
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
        array $errors = []
    ): ResponseInterface {
        $view = Twig::fromRequest($request);
        return $view->render(
            $response,
            'admin.html.twig',
            [
                'game' => $game->jsonSerialize(),
                'errors' => $errors ?? []
            ]
        );
    }

    private GameService $gameService;
    private Persistence $persistence;
    private UserSession $session;
    private Repository $repository;
}