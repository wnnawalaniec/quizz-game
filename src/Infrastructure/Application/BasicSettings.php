<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Application;

use Wojciech\QuizGame\Application\Settings;

class BasicSettings implements Settings
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function get(string $key = '')
    {
        return (empty($key)) ? $this->settings : $this->settings[$key];
    }
}