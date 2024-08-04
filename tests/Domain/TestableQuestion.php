<?php

declare(strict_types=1);

namespace Tests\Wojciech\QuizGame\Domain;

use Wojciech\QuizGame\Domain\Question;

class TestableQuestion extends Question
{
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
}