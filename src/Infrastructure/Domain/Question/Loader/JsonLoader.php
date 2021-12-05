<?php
declare(strict_types=1);

namespace Wojciech\QuizGame\Infrastructure\Domain\Question\Loader;

use Wojciech\QuizGame\Domain\Answer;
use Wojciech\QuizGame\Domain\Question;
use Wojciech\QuizGame\Infrastructure\Domain\Question\Loader;

class JsonLoader implements Loader
{
    public function __construct(string $json)
    {
        $this->json = $json;
    }

    public function load(): array
    {
        $data = json_decode($this->json, true);

        if ($data === false) {
            throw new \RuntimeException('File is not valid json');
        }

        if (!isset($data['questions']) || !is_array($data['questions'])) {
            throw new \RuntimeException('Invalid file - no questions founded');
        }

        $questions = [];
        foreach ($data['questions'] as $questionNo => $question) {
            if (!isset($question['text']) || !is_string($question['text'])) {
                throw new \RuntimeException('Invalid file - question must have text');
            }

            if (!isset($question['answers']) || !is_array($question['answers'])) {
                throw new \RuntimeException('Invalid file - question must have answers');
            }

            $answers = [];
            foreach ($question['answers'] as $answerNo => $answer) {
                if (!isset($answer['value']) || !is_string($answer['value'])) {
                    throw new \RuntimeException('Invalid file - answer must have value');
                }

                if (!isset($answer['is_correct']) || !is_bool($answer['is_correct'])) {
                    throw new \RuntimeException('Invalid file - answer is correct must be boolean');
                }
                $answers[] = new Answer($answer['value'], $answerNo+1, $answer['is_correct']);
            }

            $questions[] = new Question($questionNo+1, $question['text'], $answers);
        }

        return $questions;
    }

    private string $json;
}