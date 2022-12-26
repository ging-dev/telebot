<?php

class Question
{
    private const IMAGE_URL = 'https://e.gamevui.vn/web/2014/10/batchu/assets/pics/';

    public function __construct(
        private readonly string $image,
        private readonly string $result
    ) {
    }

    public function getImage(): string
    {
        return self::IMAGE_URL.$this->image;
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
