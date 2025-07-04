<?php

namespace App\Service;

class TeamNameMapper
{
    private array $teamNameMapping = [
        'Waterford United' => 'Waterford',
        'Dundalk FC' => 'Dundalk',
        'Fylkir FC' => 'Fylkir',
    ];

    public function getMappedTeamName(string $teamName): string
    {
        return $this->teamNameMapping[$teamName] ?? $teamName;
    }
}
