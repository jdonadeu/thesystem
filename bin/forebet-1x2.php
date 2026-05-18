<?php

include 'utils.php';
include 'lib/ForeBet.php';

$pct = 80;
$foreBet = new Forebet();
$foreBetMatches = $foreBet->getMatches(false);

echo "\n\n";
echo "****************************************************** \n";
echo "* 1X2 \n";
echo "****************************************************** \n";

foreach ($foreBetMatches as $match) {
    if ($match['homePct'] < $pct && $match['awayPct'] < $pct) {
        //continue;
    }

    $goals = explode('-', $match['host_sc_pr']);

    if ((int)$goals[0] + (int)$goals[1] < 5) {
        continue;
    }

    echo "-- " . implode(",", $match) . "\n";
}
