<?php

include 'Zulu.php';
include 'ForeBet.php';

class MatchCollector
{
    public function getMatches(): array
    {
        $zulu = new Zulu();
        $zuluMatches = $zulu->getMatches();

        $foreBet = new ForeBet();
        $foreBetMatches = $foreBet->getMatches();

        //usort($zuluMatches, "compareByHomePct");

        return $this->matchMerger($zuluMatches, $foreBetMatches);
    }

    private function matchMerger(array $a, array $b): array
    {
        $mergedData = [];

        foreach ($a as $aRow) {
            $newRow = $aRow;

            foreach ($b as $bRow) {
                $aTeams = $aRow[1];
                $bTeams = $bRow[1];

                similar_text($aTeams, $bTeams, $pct);

                var_dump($pct);

                if ($pct > 50) {
                    $newRow[] = "    ";
                    $newRow = array_merge($newRow, $bRow);
                    $newRow[] = "    ";
                    $newRow[] = "TOTALS";

                    $aPrediction_1 = $aRow[3];
                    $bPrediction_1 = $bRow[3];
                    $aPrediction_X = $aRow[4];
                    $bPrediction_X = $bRow[4];
                    $aPrediction_2 = $aRow[5];
                    $bPrediction_2 = $bRow[5];

                    $newRow[] = $aPrediction_1 + $bPrediction_1;
                    $newRow[] = $aPrediction_X + $bPrediction_X;
                    $newRow[] = $aPrediction_2 + $bPrediction_2;

                    break;
                }
            }

            $mergedData[] = $newRow;
        }

        return $mergedData;
    }


    private function compareByHomePct($a, $b): int {
        return $b[2] - $a[2];
    }
}
