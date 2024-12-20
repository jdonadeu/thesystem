<?php

class MatchCollector
{
    public function getMatches(): array
    {
        $zulu = new Zulu();
        $foreBet = new ForeBet();

        $allMatches = $this->matchMerger($zulu->getMatches(), $foreBet->getMatches());
        usort($allMatches, [$this, "compareByTotalHomePct"]);

        return $allMatches;
    }

    private function matchMerger(array $a, array $b): array
    {
        $mergedData = [];

        foreach ($a as $aRow) {
            $newRow = $aRow;

            foreach ($b as $bRow) {
                $aTeams = $aRow['teams'];
                $bTeams = $bRow['teams'];

                similar_text($aTeams, $bTeams, $pct);

                if ($pct > 75) {
                    $newRow[] = $pct;
                    $newRow[] = "    ";
                    $newRow = array_merge(array_values($newRow), array_values($bRow));
                    $newRow[] = "    ";
                    $newRow[] = "TOTALS";
                    $newRow['totalHomePct'] = $aRow['homePct'] + $bRow['homePct'];
                    $newRow['totalDrawPct'] = $aRow['drawPct'] + $bRow['drawPct'];
                    $newRow['totalAwayPct'] = $aRow['awayPct'] + $bRow['awayPct'];

                    break;
                }
            }

            $mergedData[] = $newRow;
        }

        return $mergedData;
    }

    private function compareByTotalHomePct($a, $b): int {
        $aValue = $a['totalHomePct'] ?? 0;
        $bValue = $b['totalHomePct'] ?? 0;

        return $bValue - $aValue;
    }
}
