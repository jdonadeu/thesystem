<?php

include 'MatchCollector.php';

$filename = 'matches.csv';

$matchCollector = new MatchCollector();
$matches = $matchCollector->getMatches();

$fp = fopen($filename, 'w');

foreach ($matches as $match) {
    fputcsv($fp, $match);
}

fclose($fp);

echo "\n\n";
echo "****************************************************** \n";
echo "* \n";
echo "* System Linea del Tubo FINISHED \n";
echo "* YAAAAASSTAAAAAAAAAAAAA \n";
echo "* Suerte y gaceta hipica \n";
echo "* \n";
echo "****************************************************** \n";