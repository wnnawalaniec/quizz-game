<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use JetBrains\PhpStorm\Pure;

/**
 * @Entity
 * @ORM\Table(name="answer")
 */
class Answer
{
    public function __construct(string $value, int $number, bool $isCorrect)
    {
        $this->value = $value;
        $this->number = $number;
        $this->isCorrect = $isCorrect;
    }

    #[Pure] public static function createCorrect(string $value, int $number): self
    {
        return new self($value, $number, true);
    }

    #[Pure] public static function createIncorrect(string $value, int $number): self
    {
        return new self($value, $number, false);
    }

    public function number(): int
    {
        return $this->number;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private int $id;
    /** @ORM\Column(type="string") */
    private string $value;
    /**
     * @ORM\Column(type="integer")
     */
    private int $number;
    /** @ORM\Column(type="boolean") */
    private bool $isCorrect;
    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="answers")
     */
    private int $question;
}