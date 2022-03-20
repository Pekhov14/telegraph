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

    protected function configure(): void
    {
        $this
            ->addArgument('input_file', InputArgument::REQUIRED, 'Путь к файлу')
            ->addArgument('output_file', InputArgument::OPTIONAL, 'Путь для файла резуатата')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progressBar = new ProgressBar($output, 5);

        $progressBar->start();

        $i = 0;
        while ($i++ < 5) {
            sleep(1);
            $progressBar->advance();
        }
        // Метод для чтения файла
        sleep(1);

        $output->writeln([
            '',
            '',
            'Выполнено успешно',
        ]);

        $output->writeln('input_file: '.$input->getArgument('input_file'));
        $output->writeln('output_file: '.$input->getArgument('output_file'));

        $progressBar->finish();

        return Command::SUCCESS;
    }
}