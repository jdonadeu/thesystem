<?php

include 'lib/MatchCollector.php';
include 'lib/Zulu.php';
include 'lib/ForeBet.php';

// Zulu matches
$zulu = new Zulu();
saveCsvFile('csv/zulu-matches.csv', $zulu->getMatches());

// ForeBet files
$foreBet = new Forebet();
saveCsvFile('csv/forebet-matches.csv', $foreBet->getMatches());

// All matches
$matchCollector = new MatchCollector();
saveCsvFile('csv/all-matches.csv', $matchCollector->getMatches());

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