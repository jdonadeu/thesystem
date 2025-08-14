<?php

namespace App\Command\Forebet;

use App\Tipster\ForeBet;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forebet:import')]
class Import extends Command
{
    public function __construct(private readonly ForeBet $foreBet)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('date', InputArgument::OPTIONAL);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $this->foreBet->importMatches();
        $this->foreBet->persistMatches();
        echo "\n";

        return Command::SUCCESS;
    }
}

