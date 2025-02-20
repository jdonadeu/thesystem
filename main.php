<?php

include 'lib/MatchMerger.php';
include 'lib/Zulu.php';
include 'lib/ForeBet.php';

$zulu = new Zulu();
$foreBet = new Forebet();
$matchMerger = new MatchMerger();

// Zulu matches
$zuluMatches = $zulu->getMatches();
saveCsvFile('csv/zulu-1x2.csv', $zuluMatches);

// ForeBet matches
$foreBetMatches = $foreBet->getMatches();
saveCsvFile('csv/forebet-1x2.csv', $foreBetMatches);
saveCsvFile('csv/forebet-under-over.csv', $foreBet->getUnderOverMatches());
saveCsvFile('csv/forebet-bts.csv', $foreBet->getBothToScoreMatches());

// All matches
saveCsvFile('csv/zulu-forebet-1x2.csv', $matchMerger->getMatches($zuluMatches, $foreBetMatches));

// Output
echo "\n\n";
echo "****************************************************** \n";
echo "* \n";
echo "* System Linea del Tubo FINISHED \n";
echo "* YAAAAASSTAAAAAAAAAAAAA \n";
echo "* Suerte y gaceta hipica \n";
echo "* \n";
echo "****************************************************** \n";
echo "\n\n";

// Functions
function saveCsvFile(string $filename, array $data): void
{
    $fp = fopen($filename, 'w');

    foreach ($data as $match) {
        fputcsv($fp, $match);
    }

    fclose($fp);
}
