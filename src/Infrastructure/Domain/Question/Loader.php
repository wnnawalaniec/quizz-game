<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Domain\Question;

use Wojciech\QuizGame\Domain\Question;

interface Loader
{
     /** @return Question[] */
    public function load(): array;
}