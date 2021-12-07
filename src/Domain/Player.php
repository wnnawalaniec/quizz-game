<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use JetBrains\PhpStorm\Pure;
use Ramsey\Uuid\Doctrine\UuidGenerator;

/**
 * @Entity
 * @ORM\Table(name="player")
 */
class Player
{
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function id(): string
    {
        return $this->id;
    }

    #[Pure] public function equals(Player $player): bool
    {
        return $this->id === $player->id
            && $this->game->id() === $player->game->id();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private string $id;

    /** @ORM\Column(type="string") */
    private string $name;
    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="players")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private Game $game;
}