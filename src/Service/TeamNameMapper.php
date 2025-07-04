<?php

namespace App\Service;

class TeamNameMapper
{
    private array $teamNameMapping = [];

    public function getMappedTeamName(string $teamName): string
    {
        return $teamName;
    }
}
