<?php

namespace App\Command\TodayFootballPrediction;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'todayfootballprediction:import')]
class TodayFootballPrediction extends Command
{
    protected function configure(): void
    {
        $this->addArgument('date', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');
        $api = new \App\Tipster\TodayFootballPrediction();
        $api->import($date);

        return Command::SUCCESS;
    }
}

