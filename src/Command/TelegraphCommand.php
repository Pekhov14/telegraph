<?php

namespace App\Command;

use App\Service\FileManager;
use App\Service\ParagraphGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Helper\ProgressBar;

class TelegraphCommand extends Command
{
    protected static $defaultName = 'bot:create-paragraph';
    private string $spaces = '';
    private string $projectDir;
    private int $pageNumber = 1;

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
        # Directory preparation
        $inputFile  = $this->projectDir . '/public/input/' . $input->getArgument('input_file');
        $outputFile = $this->projectDir . '/public/output/' . $input->getArgument('output_file');

        $paragraphGenerator = new ParagraphGenerator();
        $fileManager         = new FileManager();

        $this->spaces = $paragraphGenerator->setSizeSpaces($input->getArgument('spaces_count'));

        $fileManager->clearFolder($this->projectDir . '/public/output', '/*');

        # Get all lines from file
        $lines = file($inputFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        $progressBar = new ProgressBar($output, count($lines));
        $progressBar->start();

        $this->splitByDot($lines, $outputFile, $progressBar, $fileManager);

        $progressBar->finish();

        return Command::SUCCESS;
    }

    # TODO: In the future, move to Paragraph Generator
    private function splitByDot(array $lines, string $outputFile, ProgressBar $progressBar, FileManager $fileManager): void
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
                $fileManager->writeContent($outputFile, $paragraph,$this->pageNumber);

                continue;
            }

            $paragraph = $this->spaces . $paragraph;

            $fileManager->writeContent($outputFile, $paragraph, $this->pageNumber);

            $progressBar->advance();
            usleep(1000);
        }

    }
}