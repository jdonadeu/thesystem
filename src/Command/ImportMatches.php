<?php

namespace App\Command;

use App\Tipsters\Zulu;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'matches:import')]
class ImportMatches
{
    public function __construct(private readonly Zulu $zulu)
    {}

    public function __invoke(): int
    {
        $this->zulu->importMatches();
        return Command::SUCCESS;
    }
}

