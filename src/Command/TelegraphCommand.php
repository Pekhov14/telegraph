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
    private $projectDir;


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
        # Read file
        $inputFile  = $this->projectDir . '/public/input/' . $input->getArgument('input_file');
        $outputFile = $this->projectDir . '/public/output/' . $input->getArgument('output_file');


        $this->setSpaces($input->getArgument('spaces_count'));

        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        $lines = file($inputFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        $progressBar = new ProgressBar($output, count($lines));

        $progressBar->start();

        $this->splitByDot($lines, $outputFile, $progressBar);

        $progressBar->finish();

        return Command::SUCCESS;
    }

    private function splitByDot($lines, $outputFile, $progressBar): void
    {
        foreach($lines as $key => $line) {
            $paragraph = $line . "\n";

            $length = mb_strlen($line);

            if ($length < 120) {
                file_put_contents(
                    $outputFile,
                    $this->spaces . $paragraph,
                    FILE_APPEND | LOCK_EX
                );
            } else if ($length > 400) {
                $textArray = explode(".", $line);

                $textParagraph = '';
                $tmpText = '';

                foreach ($textArray as $textIndex => $textValue) {
                    if ($textIndex === 0) {
                        if (mb_strlen($textValue) > 400) {
                            $textParagraph .= $this->spaces . $textValue . ".\n";
                        }

                        $tmpText = $textValue . '.';
                        continue;
                    }

                    if (mb_strlen($tmpText . $textValue) > 400) {
                        $textParagraph .= $this->spaces . $tmpText . "\n";

                        $tmpText = $textValue  . '.';
                        continue;
                    }


                    $tmpText .= $textValue . '.';

                    if(!next($textArray)) {
                        $textParagraph .= $tmpText . '.';
                    }
                }

//                $this->spaces .
                $paragraph =  $textParagraph;

                file_put_contents(
                    $outputFile,
                    $paragraph,
                    FILE_APPEND | LOCK_EX
                );
            } else {
                $paragraph = $this->spaces . $paragraph;

                file_put_contents(
                    $outputFile,
                    $paragraph,
                    FILE_APPEND | LOCK_EX
                );
            }

            $progressBar->advance();
            usleep(1000);
        }

    }

    private function setSpaces($spaces_count): void
    {
        for ($i = 0; $i < $spaces_count; $i++) {
            $this->spaces .= ' ';
        }
    }
}