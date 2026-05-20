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
    if ($match['goalsavg'] < 3.5) {
        continue;
    }

    $goals = explode('-', $match['host_sc_pr']);

    if ((int)$goals[0] < 2 || (int)$goals[1] < 2) {
        continue;
    }

    echo "-- " . implode(",", $match) . "\n";
}
