<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @Entity
 * @ORM\Table(name="player")
 */
class Player implements \JsonSerializable
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
        return (string) $this->id;
    }

    #[Pure] public function equals(Player $player): bool
    {
        return $this->id === $player->id
            && $this->game->id() === $player->game->id();
    }

    public function name(): string
    {
        return $this->name;
    }

    #[Pure]
    #[ArrayShape(['id' => "string", 'name' => "string"])]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name
        ];
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private UuidInterface $id;
    /** @ORM\Column(type="string") */
    private string $name;
    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="players")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private Game $game;
    /**
     * @ORM\OneToMany(targetEntity="Score", mappedBy="player")
     * @var Collection<Score>
     */
    private Collection $scores;
}