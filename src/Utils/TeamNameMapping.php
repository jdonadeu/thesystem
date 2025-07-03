<?php

namespace TheSystem\Utils;

class TeamNameMapping
{
    private array $teamNameMapping = [];

    public function getMappedTeamName(string $teamName): string
    {
        return $teamName;
    }
}
