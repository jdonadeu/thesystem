<?php

include 'utils.php';
include 'lib/ForeBet.php';

$pct = 80;
$foreBet = new Forebet();
$foreBetMatchesUnderOver = $foreBet->getBothToScoreMatches();

echo "\n\n";
echo "****************************************************** \n";
echo "* BTS \n";
echo "****************************************************** \n";

foreach ($foreBetMatchesUnderOver as $match) {
    if ($match['yesPct'] < $pct) {
        continue;
    }

    echo "-- " . implode(",", $match) . "\n";
}
