<?php

include 'lib/MatchMerger.php';
include 'lib/Zulu.php';
include 'lib/ForeBet.php';

$zulu = new Zulu();
$foreBet = new Forebet();
$matchMerger = new MatchMerger();

// Zulu matches
$zuluMatches1x2 = $zulu->getMatches();
saveCsvFile('csv/zulu-1x2.csv', $zuluMatches1x2);

// ForeBet matches
$foreBetMatches1x2 = $foreBet->getMatches();
$foreBetMatchesUnderOver = $foreBet->getUnderOverMatches();
$foreBetMatchesBts = $foreBet->getBothToScoreMatches();
saveCsvFile('csv/forebet-1x2.csv', $foreBetMatches1x2);
saveCsvFile('csv/forebet-under-over.csv', $foreBetMatchesUnderOver);
saveCsvFile('csv/forebet-bts.csv', $foreBetMatchesBts);

// All matches
$zuluForeBetMatches1x2 = $matchMerger->getMatches($zuluMatches1x2, $foreBetMatches1x2);
saveCsvFile('csv/zulu-forebet-1x2.csv', $zuluForeBetMatches1x2);

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

// 1X2
echo "****************************************************** \n";
echo "* 1X2 \n";
echo "****************************************************** \n";

foreach ($zuluForeBetMatches1x2 as $match) {
    if (($match['totalHomePct'] ?? 0) < 120) {
        continue;
    }

    echo "-- " . implode(",", $match) . "\n";
}

// Under over
echo "\n\n";
echo "****************************************************** \n";
echo "* Under over \n";
echo "****************************************************** \n";

foreach ($foreBetMatchesUnderOver as $match) {
    if ($match['overPct'] < 90) {
        continue;
    }

    echo "-- " . implode(",", $match) . "\n";
}

// Under over
echo "\n\n";
echo "****************************************************** \n";
echo "* BTS \n";
echo "****************************************************** \n";

foreach ($foreBetMatchesBts as $match) {
    if ($match['yesPct'] < 90) {
        continue;
    }

    echo "-- " . implode(",", $match) . "\n";
}


// Functions
function saveCsvFile(string $filename, array $data): void
{
    $fp = fopen($filename, 'w');

    foreach ($data as $match) {
        fputcsv($fp, $match);
    }

    fclose($fp);
}
