<?php

namespace App\Service;

use App\Tipster\ForeBet;
use App\Tipster\Zulu;

class MatchMerger
{
    public function __construct(private readonly Zulu $zulu)
    {}

    public function getMatches(array $zuluMatches = [], array $foreBetMatches = []): array
    {
        if (empty($zuluMatches)) {
            $zuluMatches = $this->zulu->getMatches();
        }

        if (empty($foreBetMatches)) {
            $foreBetMatches = (new ForeBet())->getMatches();
        }

        $allMatches = $this->merge($zuluMatches, $foreBetMatches);

        usort($allMatches, function($a, $b) {
            return ($b['totalHomePct'] ?? 0) - ($a['totalHomePct'] ?? 0);
        });

        return $allMatches;
    }

    private function merge(array $a, array $b): array
    {
        $mergedData = [];

        foreach ($a as $aRow) {
            $newRow = $aRow;

            foreach ($b as $bRow) {
                $aTeams = $aRow['teams'];
                $bTeams = $bRow['teams'];

                similar_text($aTeams, $bTeams, $pct);

                if ($pct > 60) {
                    $newRow[] = floor($pct);
                    $newRow[] = "    ";
                    $newRow = array_merge(array_values($newRow), array_values($bRow));
                    $newRow[] = "    ";
                    $newRow[] = "TOTALS";
                    $newRow['totalHomePct'] = $aRow['homePct'] + $bRow['homePct'];
                    $newRow['totalDrawPct'] = $aRow['drawPct'] + $bRow['drawPct'];
                    $newRow['totalVisitorPct'] = $aRow['visitorPct'] + $bRow['visitorPct'];
                }
            }

            $mergedData[] = $newRow;
        }

        return $mergedData;
    }
}
