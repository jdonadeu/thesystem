<?php

include 'utils.php';
include 'lib/ForeBet.php';

$pct = 80;

$foreBet = new Forebet();

$foreBetMatchesUnderOver = $foreBet->getUnderOverMatches();
saveCsvFile('csv/forebet-under-over.csv', $foreBetMatchesUnderOver);

echo "\n\n";
echo "****************************************************** \n";
echo "* +2,5 \n";
echo "****************************************************** \n";

foreach ($foreBetMatchesUnderOver as $match) {
    if ($match['overPct'] < $pct) {
        continue;
    }

    echo "INSERT INTO forebet_over (date, time, home_team, visitor_team, odd_over_25) VALUES ('".$match['date']."','".$match['time']."','".$match['homeTeam']."','".$match['visitorTeam']."', );\n";
}

foreach ($foreBetMatchesUnderOver as $match) {
    if ($match['overPct'] < $pct) {
        continue;
    }

    echo $match['homeTeam']. " - ". $match['visitorTeam'] . "\n";
}
