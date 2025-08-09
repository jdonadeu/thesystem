<?php

namespace App\Command;

use App\Repository\ReportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'apifootball:run')]
class ApiFootball extends Command
{
    public function __construct(private readonly ReportRepository $reportRepository)
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $apiFootball = new \App\Tipster\ApiFootball();

        $apiFootball->go();

        return Command::SUCCESS;
    }
}

