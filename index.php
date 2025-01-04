<?php

echo '<html>';

echo '
<style>

td {
    padding: 5px; border: 1px solid black;
}

</style>';

echo '<body>';

// All matches
$allMatchesFilename = 'csv/all-matches.csv';
echo '<p>All</p>';
printTable($allMatchesFilename);

// Zulu matches
$zuluFilename = 'csv/zulu-matches.csv';
echo '<p>Zulu</p>';
printTable($zuluFilename);

// ForeBet files
$foreBetFilename = 'csv/forebet-matches.csv';
echo '<p>Forebet</p>';
printTable($foreBetFilename);

echo '</body>';
echo '</html>';

function printTable(string $filename): void
{
    $data = htmlspecialchars(file_get_contents($filename));
    $rows = explode("\n", $data);

    echo '<table style="border: 1px solid black;">';

    foreach ($rows as $row) {
        $cols = explode(",", $row);
        echo '<tr>';

        foreach ($cols as $col) {
            echo '<td>'
                . str_replace('&quot;', '', $col)
                . '</td>';
        }

        echo '</tr>';
    }

    echo '</table>';
}