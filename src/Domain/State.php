<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Domain;

enum State: string
{
    case NEW_GAME = 'NEW_GAME';
}