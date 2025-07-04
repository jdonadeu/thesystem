<?php

namespace App\Command;

use App\Tipsters\Zulu;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'matches:persist')]
class PersistMatches extends Command
{
    public function __construct(private readonly Zulu $zulu)
    {
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->zulu->persistMatches($input->getOption('commit'));
        return Command::SUCCESS;
    }
}

