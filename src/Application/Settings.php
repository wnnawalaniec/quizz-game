<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Application;

interface Settings
{
    public function get(string $key = '');
}