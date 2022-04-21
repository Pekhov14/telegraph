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
    private string $projectDir;
    private int $pageNumber = 1;


    private FileManager $fileManager;
    private ParagraphGenerator $paragraphGenerator;

    public function __construct($projectDir, ParagraphGenerator $paragraphGenerator, FileManager $fileManager)
    {
        $this->projectDir = $projectDir;

        $this->paragraphGenerator = $paragraphGenerator;
        $this->fileManager         = $fileManager;

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

        $spaces = $this->paragraphGenerator->setSizeSpaces($input->getArgument('spaces_count'));

        $this->fileManager->clearFolder($this->projectDir . '/public/output', '/*');

        # Get all lines from file
        $lines = file($inputFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        # Configure ProgressBar
        $progressBar = new ProgressBar($output, count($lines));
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>➤</>");
        $progressBar->start();

        foreach($lines as $line) {
            $paragraph = $this->paragraphGenerator->generateParagraph($line . "\n", $spaces);

            $this->fileManager->writeContent($outputFile, $paragraph, $this->pageNumber);

            $progressBar->advance();
            usleep(1000);
        }

        $progressBar->finish();
        return Command::SUCCESS;
    }
}