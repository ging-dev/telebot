<?php

class Question
{
    public function __construct(
        public readonly string $image,
        public readonly string $result
    ) {
    }
}
