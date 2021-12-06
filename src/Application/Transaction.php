<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Application;

interface Transaction
{
    public function begin(): void;
    public function commit(): void;
    public function flush(): void;
    public function rollback(): void;
}