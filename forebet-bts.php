<?php

include 'utils.php';
include 'lib/ForeBet.php';

$foreBet = new Forebet();

$foreBetMatchesBts = $foreBet->getBothToScoreMatches();
saveCsvFile('csv/forebet-bts.csv', $foreBetMatchesBts);

echo "\n\n";
echo "****************************************************** \n";
echo "* BTS \n";
echo "****************************************************** \n";

foreach ($foreBetMatchesBts as $match) {
    if ($match['yesPct'] < 80) {
        continue;
    }

    echo "-- " . implode(",", $match) . "\n";
}
