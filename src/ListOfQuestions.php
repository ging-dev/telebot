<?php

class ListOfQuestions
{
    /** @param array<Question> $questions */
    public function __construct(
        public array $questions
    ) {}

    /**
     * @return Question|null
     */
    public function pop()
    {
        return array_shift($this->questions);
    }
}
