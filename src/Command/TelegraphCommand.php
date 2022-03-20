<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        // Метод для чтения файла
        sleep(1);
        $output->writeln([
            'Читаю файл',
            '============',
            '',
        ]);
        sleep(1);

        $output->writeln([
            'Создаю переносы строк',
            '============',
            '',
        ]);
        sleep(1);

        $output->writeln([
            'Создаю отпупы',
            '============',
            '',
        ]);

        $output->writeln('input_file: '.$input->getArgument('input_file'));
        $output->writeln('output_file: '.$input->getArgument('output_file'));

        return Command::SUCCESS;
    }
}