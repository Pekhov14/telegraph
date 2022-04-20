<?php

namespace App\Service;

class FileManager
{
    private const SIZE_FOR_PAGE = 35;

    public function clearFolder(string $pathOutFolder, $filter = '/*'): void
    {
        # Clear all /*.txt files
        array_map('unlink', glob( $pathOutFolder . $filter));
    }

    public function writeContent(string $outputFile, string $text, int &$pageNumber): void
    {
        $filename = $outputFile . $pageNumber . '.txt';

        clearstatcache();

        // kilobytes with two digits
        if (file_exists($filename) && (round(filesize($filename) / 1024, 2)) > self::SIZE_FOR_PAGE) {
            $pageNumber++;
        }

        file_put_contents(
            $outputFile . $pageNumber . '.txt',
            $text,
            FILE_APPEND | LOCK_EX
        );
    }
}