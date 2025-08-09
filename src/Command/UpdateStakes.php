<?php

namespace App\Command;

use App\Repository\ReportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'update:stakes')]
class UpdateStakes extends Command
{
    public function __construct(private readonly ReportRepository $reportRepository)
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $this->reportRepository->updateStakes();
        return Command::SUCCESS;
    }
}

