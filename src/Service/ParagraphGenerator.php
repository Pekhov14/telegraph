<?php

namespace App\Service;

class ParagraphGenerator
{
    private const MAX_LENGTH_PARAGRAPH = 400;

    public function setSizeSpaces(int $numberSpaces): string
    {
        return str_repeat(' ', $numberSpaces);
    }

    public function generateParagraph(string $lineOfText, string $spaces): string
    {
        $length = mb_strlen($lineOfText);

        if ($length > self::MAX_LENGTH_PARAGRAPH) {
            return $this->getSplitParagraph($lineOfText, $spaces);
        }

        return $spaces . $lineOfText;
    }

    private function getSplitParagraph(string $string, string $spaces): string
    {
        $paragraph = "";

        $linesToDot = explode('.', $string);

        $tmpText = '';

        foreach ($linesToDot as $textValue) {
            if (empty($textValue)) {
                continue;
            }

            if (mb_strlen($tmpText . $textValue) > self::MAX_LENGTH_PARAGRAPH) {
                $paragraph .= $spaces . $tmpText . "\n";

                $tmpText = $textValue  . '.';
                continue;
            }

            # I fill the paragraph with text until it exceeds 400 characters
            $tmpText .= $textValue . '.';

            # If the array is over, release the temporary data
            if(!next($linesToDot)) {
                $paragraph .= $tmpText . '.';
            }
        }

        return $paragraph;
    }
}