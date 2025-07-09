<?php

namespace App\Command;

use App\Repository\EventRepository;
use App\Tipster\ForeBet;
use App\Tipster\Zulu;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'matches:persist')]
class PersistMatches extends Command
{
    public function __construct(
        private readonly Zulu $zulu,
        private readonly ForeBet $foreBet,
        private readonly EventRepository $eventRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'commit',
            null,
            InputOption::VALUE_NONE,
            'Actually commit changes to the database'
        );
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $commit = $input->getOption('commit');

        $this->zulu->persistMatches($commit);
        //$this->foreBet->persistMatches($commit);

        $this->eventRepository->removePastWithoutGoals();

        return Command::SUCCESS;
    }
}

