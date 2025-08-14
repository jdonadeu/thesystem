<?php

namespace App\Command\Forebet;

use App\Repository\ForebetRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forebet:update-stakes')]
class UpdateStakes extends Command
{
    public function __construct(private readonly ForebetRepository $forebetRepository)
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $this->forebetRepository->updateStakes();
        return Command::SUCCESS;
    }
}

