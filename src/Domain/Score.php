<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

class Score
{
    private string $player;
    private int $question;
    private string $answer;
}