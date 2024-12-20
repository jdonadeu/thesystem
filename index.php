<?php

include 'lib/MatchCollector.php';

echo '<html>';
echo '<body>';

// Zulu matches
echo '<p>Zulu</p>';
$zulu = new Zulu();
printMatches($zulu->getMatches());

// ForeBet files
echo '<p>Forebet</p>';
$foreBet = new Forebet();
printMatches($foreBet->getMatches());

// All matches
echo '<p>All</p>';
$matchCollector = new MatchCollector();
printMatches($matchCollector->getMatches());

echo '</body>';
echo '</html>';


// Functions
function saveCsvFile(string $filename, array $data): void
{
    $fp = fopen($filename, 'w');

    foreach ($data as $match) {
        fputcsv($fp, $match);
    }

    fclose($fp);
}

function printMatches(array $matches): void
{
    foreach ($matches as $match) {
        echo implode(',', $match) . "<br>";
    }
    echo "<br><br>";
}