<?php

include 'utils.php';
include 'lib/ForeBet.php';

$foreBet = new Forebet();

$foreBetMatchesUnderOver = $foreBet->getUnderOverMatches();
saveCsvFile('csv/forebet-under-over.csv', $foreBetMatchesUnderOver);

echo "\n\n";
echo "****************************************************** \n";
echo "* +2,5 \n";
echo "****************************************************** \n";

foreach ($foreBetMatchesUnderOver as $match) {
    if ($match['overPct'] < 80) {
        continue;
    }

    echo "-- " . implode(",", $match) . "\n";
}
