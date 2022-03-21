<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Helper\ProgressBar;

class TelegraphCommand extends Command
{
    protected static $defaultName = 'bot:create-paragraph';
    private string $spaces = '';
    private int $pageNumber = 1;
    private $projectDir;

    private const SIZE_FOR_PAGE = 42;
    private const MAX_LENGTH_PARAGRAPH = 400;

    public function __construct($projectDir)
    {
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('input_file', InputArgument::REQUIRED, 'File name with text')
            ->addArgument('output_file', InputArgument::REQUIRED, 'Output file name')
            ->addArgument('spaces_count', InputArgument::OPTIONAL, 'Quantity chars spaces', 20)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputFile  = $this->projectDir . '/public/input/' . $input->getArgument('input_file');
        $outputFile = $this->projectDir . '/public/output/' . $input->getArgument('output_file');


        $this->setSpaces($input->getArgument('spaces_count'));

        # Clear all .txt files
        array_map('unlink', glob( $this->projectDir . '/public/output/' . '*.txt'));

        $lines = file($inputFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        $progressBar = new ProgressBar($output, count($lines));

        $progressBar->start();

        $this->splitByDot($lines, $outputFile, $progressBar);

        $progressBar->finish();

        return Command::SUCCESS;
    }

    private function splitByDot(array $lines, string $outputFile, ProgressBar $progressBar): void
    {
        foreach($lines as $key => $line) {
            $paragraph = $line . "\n";

            $length = mb_strlen($line);

            if ($length > self::MAX_LENGTH_PARAGRAPH) {
                $textArray = explode(".", $line);

                $textParagraph = '';
                $tmpText = '';

                foreach ($textArray as $textIndex => $textValue) {
                    if (empty($textValue)) {
                        continue;
                    }

                    if ($textIndex === 0) {
                        if (mb_strlen($textValue) > self::MAX_LENGTH_PARAGRAPH) {
                            $textParagraph .= $this->spaces . $textValue . ".\n";
                        }

                        $tmpText = $textValue . '.';
                        continue;
                    }

                    if (mb_strlen($tmpText . $textValue) > self::MAX_LENGTH_PARAGRAPH) {
                        $textParagraph .= $this->spaces . $tmpText . "\n";

                        $tmpText = $textValue  . '.';
                        continue;
                    }


                    $tmpText .= $textValue . '.';

                    if(!next($textArray)) {
                        $textParagraph .= $tmpText . '.';
                    }
                }

                $paragraph = $textParagraph;

                $this->writeContent($outputFile, $paragraph);

                continue;
            }

            $paragraph = $this->spaces . $paragraph;

            $this->writeContent($outputFile, $paragraph);

            $progressBar->advance();
            usleep(1000);
        }

    }

    private function setSpaces(int $spaces_count): void
    {
        for ($i = 0; $i < $spaces_count; $i++) {
            $this->spaces .= ' ';
        }
    }

    private function writeContent(string $outputFile, string $text): void
    {
        $filename = $outputFile . $this->pageNumber . '.txt';

        clearstatcache();

        // kilobytes with two digits
        if (file_exists($filename) && (round(filesize($filename) / 1024, 2)) > self::SIZE_FOR_PAGE) {
            $this->pageNumber++;
        }

        file_put_contents(
            $outputFile . $this->pageNumber . '.txt',
            $text,
            FILE_APPEND | LOCK_EX
        );
    }
}