<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="score")
 */
class Score implements \JsonSerializable
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

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player->id(),
            'question' => $this->question->id(),
            'answer' => $this->answer->id()
        ];
    }

    public function answer(): Answer
    {
        return $this->answer;
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
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="scores")
     */
    private Player $player;
    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="scores")
     */
    private Question $question;

    /**
     * @ORM\ManyToOne(targetEntity="Answer", inversedBy="scores")
     */
    private Answer $answer;
}