<?php

namespace App\Service;

class TeamNameMapper
{
    private array $teamNameMapping = [
        'Waterford United' => 'Waterford',
        'Dundalk FC' => 'Dundalk',
        'Fylkir FC' => 'Fylkir',
        'PSG' => 'Paris Saint Germain',
    ];

    public function getMappedTeamName(string $teamName): string
    {
        return $this->teamNameMapping[$teamName] ?? $teamName;
    }
}
