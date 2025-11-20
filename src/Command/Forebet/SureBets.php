<?php

namespace App\Command\Forebet;

use App\Tipster\ApiFootball;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sure:bets')]
class SureBets extends Command
{
    protected function configure(): void
    {
        $this->addArgument('date', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');

        $api = new ApiFootball();
        $api->sureBets($date);
        return 1;
    }
}

