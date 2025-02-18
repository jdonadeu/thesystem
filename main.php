<?php

include 'lib/MatchMerger.php';
include 'lib/Zulu.php';
include 'lib/ForeBet.php';

// Zulu matches
$zulu = new Zulu();
$zuluMatches = $zulu->getMatches();
saveCsvFile('csv/zulu-matches.csv', $zuluMatches);

// ForeBet files
$foreBet = new Forebet();
$foreBetMatches = $foreBet->getMatches();
saveCsvFile('csv/forebet-matches.csv', $foreBetMatches);

// All matches
$matchMerger = new MatchMerger();
saveCsvFile('csv/all-matches.csv', $matchMerger->getMatches($zuluMatches, $foreBetMatches));

// ForeBet under over
$foreBetMatches = $foreBet->getUnderOverMatches();
saveCsvFile('csv/forebet-over-under.csv', $foreBet->getUnderOverMatches());


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
