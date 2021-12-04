<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Model;

class Answer
{
    public function __construct(string $value, string $number, bool $isCorrect)
    {
        $this->value = $value;
        $this->number = $number;
        $this->isCorrect = $isCorrect;
    }

    private string $value;
    private string $number;
    private bool $isCorrect;
}