<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

class Player
{
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    private string $id;
    private string $name;
}