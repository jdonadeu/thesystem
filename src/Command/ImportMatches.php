<?php

namespace App\Command;

use App\Tipster\ForeBet;
use App\Tipster\Zulu;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'matches:import')]
class ImportMatches
{
    public function __construct(
        private readonly Zulu $zulu,
        private readonly ForeBet $foreBet,
    ) {
    }

    public function __invoke(): int
    {
        $this->zulu->importMatches();
        $this->foreBet->importMatches();

        return Command::SUCCESS;
    }
}

