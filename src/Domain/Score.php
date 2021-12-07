<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="score")
 */
class Score
{
    /**
     * @param Game $game
     * @param Player $player
     * @param Question $question
     * @param Answer $answer
     */
    public function __construct(Game $game, Player $player, Question $question, Answer $answer)
    {
        $this->game = $game;
        $this->player = $player;
        $this->question = $question;
        $this->answer = $answer;
    }

    public function player(): Player
    {
        return $this->player;
    }

    public function question(): Question
    {
        return $this->question;
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;
    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="scores")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private Game $game;
    /**
     * @ORM\OneToOne(targetEntity="Player")
     */
    private Player $player;
    /**
     * @ORM\OneToOne(targetEntity="Question")
     */
    private Question $question;
    /**
     * @ORM\OneToOne(targetEntity="Answer")
     */
    private Answer $answer;
}