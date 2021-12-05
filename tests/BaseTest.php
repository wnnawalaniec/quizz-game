<?php
declare(strict_types=1);

namespace Tests\Wojciech\QuizGame;

use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    protected function assertException(\Exception $expectedException, callable $act): void
    {
        $this->expectExceptionObject($expectedException);
        $act();
    }

    protected function assertNoExceptions(): void
    {
        $this->assertTrue(true);
    }
}