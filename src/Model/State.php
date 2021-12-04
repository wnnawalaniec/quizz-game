<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Model;

enum State: string
{
    case NEW_GAME = 'NEW_GAME';
}