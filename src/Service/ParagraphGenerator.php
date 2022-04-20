<?php

namespace App\Service;

class ParagraphGenerator
{
    public function setSizeSpaces(int $numberSpaces): string
    {
        return str_repeat(' ', $numberSpaces);
    }
}