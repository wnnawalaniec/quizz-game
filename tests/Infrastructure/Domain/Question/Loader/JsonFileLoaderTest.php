<?php
declare(strict_types=1);

namespace Tests\Wojciech\QuizGame\Infrastructure\Domain\Question\Loader;

use PHPUnit\Framework\TestCase;
use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Infrastructure\Domain\Question\Loader\JsonLoader;

class JsonFileLoaderTest extends TestCase
{
    public function testLoadingJson_CorrectJsonGiven_QuestionsAreLoaded(): void
    {
        $json = file_get_contents(__DIR__ . '/test.json');
        $loader = new JsonLoader($json);

        $questions = $loader->load();

        $expectedQuestions = [
            new Question(1, '1+1=?', [
                Answer::createIncorrect('1', 1),
                Answer::createCorrect('2', 2)
            ])
        ];
        $this->assertEquals($expectedQuestions, $questions);
    }
}
