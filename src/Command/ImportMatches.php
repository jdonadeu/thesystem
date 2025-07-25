<?php

namespace App\Command;

use App\Tipster\ForeBet;
use App\Tipster\Zulu;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'matches:import')]
class ImportMatches extends Command
{
    public function __construct(
        private readonly Zulu $zulu,
        private readonly ForeBet $foreBet,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tipsterId', InputArgument::REQUIRED);
        $this->addArgument('date', InputArgument::OPTIONAL);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = (int)$input->getArgument('tipsterId');

        if ($tipsterId === 1) {
            $this->zulu->importMatches($input->getArgument('date'));
        } elseif ($tipsterId === 2) {
            $this->foreBet->importMatches();
            $this->foreBet->persistMatches();
        }

        echo "\n";

        return Command::SUCCESS;
    }
}

