<?php

include 'utils.php';
include 'lib/MatchMerger.php';
include 'lib/Zulu.php';
include 'lib/ForeBet.php';
include 'lib/PronosticosFutbol365.php';

$zulu = new Zulu();
$foreBet = new Forebet();
$pronosticosFutbol365 = new PronosticosFutbol365();
$matchMerger = new MatchMerger();

// Zulu matches
$zuluMatches1x2 = $zulu->getMatches();
saveCsvFile('csv/zulu-1x2.csv', $zuluMatches1x2);

// ForeBet matches
$foreBetMatches1x2 = $foreBet->getMatches();
$foreBetMatchesUnderOver = $foreBet->getUnderOverMatches();
$foreBetMatchesBts = $foreBet->getBothToScoreMatches();
saveCsvFile('csv/forebet-1x2.csv', $foreBetMatches1x2);
saveCsvFile('csv/forebet-1x2-includes-past-matches.csv', $foreBet->getMatches(false));
saveCsvFile('csv/forebet-under-over.csv', $foreBetMatchesUnderOver);
saveCsvFile('csv/forebet-bts.csv', $foreBetMatchesBts);

// PronosticosFutbol365
$pronosticosFutbol365Matches = $pronosticosFutbol365->getMatches();

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
    if (($match['totalHomePct'] ?? 0) < 110) {
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

// Zulu matches ordered by home pct
echo "\n\n";
echo "****************************************************** \n";
echo "* Zulu ordered by homePct\n";
echo "****************************************************** \n";

usort($zuluMatches1x2, function ($item1, $item2) {
    return $item2['homePct'] <=> $item1['homePct'];
});

foreach ($zuluMatches1x2 as $match) {
    if ($match['homePct'] < 50) {
        continue;
    }
    echo "-- " . implode(",", $match) . "(" . $match['homePct'] . ")\n";
}

// Forebet matches ordered by home pct
echo "\n\n";
echo "****************************************************** \n";
echo "* Forebet ordered by homePct\n";
echo "****************************************************** \n";

usort($foreBetMatches1x2, function ($item1, $item2) {
    return $item2['homePct'] <=> $item1['homePct'];
});

foreach ($foreBetMatches1x2 as $match) {
    if ($match['homePct'] < 50) {
        continue;
    }
    echo "-- " . implode(",", $match) . "(" . $match['homePct'] . ")\n";
}

// Forebet matches ordered by home pct (includes also past matches)
echo "\n\n";
echo "****************************************************** \n";
echo "* Forebet ordered by homePct (includes also past matches)\n";
echo "****************************************************** \n";
$foreBetMatches1x2 = $foreBet->getMatches(false);

usort($foreBetMatches1x2, function ($item1, $item2) {
    return $item2['homePct'] <=> $item1['homePct'];
});

foreach ($foreBetMatches1x2 as $match) {
    if ($match['homePct'] < 50) {
        continue;
    }
    echo "-- " . implode(",", $match). "\n";
}

// PronosticosFutbol365 matches ordered by home pct
echo "\n\n";
echo "****************************************************** \n";
echo "* PronosticosFutbol365 ordered by homePct\n";
echo "****************************************************** \n";

usort($pronosticosFutbol365Matches, function ($item1, $item2) {
    return $item2['homePct'] <=> $item1['homePct'];
});

foreach ($pronosticosFutbol365Matches as $match) {
    if ($match['homePct'] < 50) {
        continue;
    }
    echo "-- " . implode(",", $match) . "(" . $match['homePct'] . ")\n";
}
