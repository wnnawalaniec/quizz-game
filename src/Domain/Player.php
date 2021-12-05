<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;

/**
 * @Entity
 * @ORM\Table(name="player")
 */
class Player
{
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private string $id;

    /** @ORM\Column(type="string") */
    private string $name;
    /** @ORM\ManyToOne(targetEntity="Game", inversedBy="players") */
    private string $game;
}