<?php

namespace App\Command;

use App\Repository\EventRepository;
use App\Tipster\ForeBet;
use App\Tipster\Zulu;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'matches:persist')]
class PersistMatches extends Command
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
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = (int)$input->getArgument('tipsterId');

        if ($tipsterId === 1) {
            $this->zulu->persistMatches();
        } elseif ($tipsterId === 2) {
            $this->foreBet->persistMatches();
        }

        // Run manually in a separate command with commit parameter
        //$this->eventRepository->removePastWithoutGoals();

        return Command::SUCCESS;
    }
}

