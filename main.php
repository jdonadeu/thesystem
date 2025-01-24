<?php

include 'lib/MatchCollector.php';
include 'lib/Zulu.php';
include 'lib/ForeBet.php';

// Zulu matches
$zulu = new Zulu();
$zuluFilename = 'csv/zulu-matches.csv';
$zuluMatches = $zulu->getMatches();
saveCsvFile($zuluFilename, $zuluMatches);

// ForeBet files
$foreBet = new Forebet();
$foreBetFilename = 'csv/forebet-matches.csv';
$foreBetMatches = $foreBet->getMatches();
saveCsvFile($foreBetFilename, $foreBetMatches);

// All matches
$matchCollector = new MatchCollector();
$allMatchesFilename = 'csv/all-matches.csv';
saveCsvFile($allMatchesFilename, $matchCollector->getMatches($zuluMatches, $foreBetMatches));

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
