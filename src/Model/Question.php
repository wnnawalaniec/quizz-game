<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Model;

class Question
{
    public function __construct(int $number, string $text, array $answers)
    {
        $this->number = $number;
        $this->text = $text;
        $this->answers = $answers;
    }

    private int $number;
    private string $text;
    /** @var Answer[] */
    private array $answers;
}