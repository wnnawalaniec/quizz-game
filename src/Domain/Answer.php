<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * @Entity
 * @ORM\Table(name="answer")
 */
class Answer implements \JsonSerializable
{
    public function __construct(string $value, bool $isCorrect)
    {
        $this->value = $value;
        $this->isCorrect = $isCorrect;
    }

    #[Pure] public static function createCorrect(string $value): self
    {
        return new self($value, true);
    }

    #[Pure] public static function createIncorrect(string $value): self
    {
        return new self($value, false);
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    #[ArrayShape(['id' => "int", 'text' => "string", 'is_correct' => "bool"])] public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->value,
            'is_correct' => $this->isCorrect
        ];
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private int $id;
    /** @ORM\Column(type="string") */
    private string $value;
    /** @ORM\Column(type="boolean") */
    private bool $isCorrect;

    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="answers")
     */
    private Question $question;
}