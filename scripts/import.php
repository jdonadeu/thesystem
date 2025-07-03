<?php

require __DIR__ . '/../vendor/autoload.php';

use TheSystem\Utils\Db;

$filename = 'data.csv';

$db = new Db();
$conn = $db->connect();

// Event
if (($handle = fopen($filename, 'r')) !== false) {
    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        $row[3] = mb_convert_encoding(utf8_decode($row[3]), 'UTF-8', 'auto');

        // Event
        $dateParts = explode("-", $row[1]);
        $teams = explode("-", $row[3]);
        $goals = explode("-", $row[32]);

        $date = "2025-$dateParts[1]-$dateParts[0]";
        $homeTeam = trim($teams[0]);
        $visitorTeam = trim($teams[1]);
        $homeGoals = $goals[0];
        $visitorGoals = $goals[1];
        $odd1 = str_replace(",", ".", $row[24]);
        $odd1X = str_replace(",", ".", $row[25]);
        $oddOver15 = str_replace(",", ".", $row[26]);
        $oddOver25 = str_replace(",", ".", $row[27]);

        $sql = "INSERT INTO events (date, homeTeam, visitorTeam, homeGoals, visitorGoals, odd1, odd1X, oddOver15, oddOver25) VALUES 
                ('$date', '$homeTeam', '$visitorTeam', $homeGoals, $visitorGoals,$odd1, $odd1X, $oddOver15, $oddOver25)";

        try {
            $conn->query($sql);
        } catch (Exception $e) {
            if ($e->getCode() !== 1062) {
                throw $e;
            }
        }
    }

    fclose($handle);
} else {
    echo "Error opening the file.";
}

// Second round: Zulu and Forebet
if (($handle = fopen($filename, 'r')) !== false) {
    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        $row[3] = mb_convert_encoding(utf8_decode($row[3]), 'UTF-8', 'auto');

        $dateParts = explode("-", $row[1]);
        $date = "2025-$dateParts[1]-$dateParts[0]";

        $teams = explode("-", $row[3]);
        $homeTeam = trim($teams[0]);
        $visitorTeam = trim($teams[1]);

        $sql = "SELECT * FROM events WHERE date = '$date'";
        $result = $conn->query($sql);
        $eventId = 0;

        while ($dbRow = $result->fetch_assoc()) {
            if ($dbRow['date'] === $date && $homeTeam === $dbRow['homeTeam'] && $visitorTeam === $dbRow['visitorTeam']) {
                $eventId = $dbRow['id'];
                echo "$homeTeam - $visitorTeam \n";
            }
        }

        if ($eventId === 0) {
            echo "***************** WARNING: match not found\n";
            echo "$homeTeam - $visitorTeam \n";
            die;
        }

        $tipsterId = 1;
        $homePct = $row[4];
        $drawPct = $row[5];
        $visitorPct = $row[6];

        $sql = "INSERT INTO predictions (eventId, tipsterId, homePct, drawPct, visitorPct) VALUES 
                ($eventId, $tipsterId, $homePct, $drawPct, $visitorPct)";

        try {
            $conn->query($sql);
        } catch (Exception $e) {
            if ($e->getCode() !== 1062) {
                throw $e;
            }
        }

        // Forebet
        $tipsterId = 2;
        $homePct = $row[14];
        $drawPct = $row[15];
        $visitorPct = $row[16];
        $avgGoals = $row[17];
        $goals = explode("-", $row[18]);
        $homeGoals = $goals[0];
        $visitorGoals = $goals[1];

        $sql = "INSERT INTO predictions (eventId, tipsterId, homePct, drawPct, visitorPct, avgGoals, homeGoals, visitorGoals) VALUES 
                ($eventId, $tipsterId, $homePct, $drawPct, $visitorPct, $avgGoals, $homeGoals, $visitorGoals)";

        try {
            $conn->query($sql);
        } catch (Exception $e) {
            if ($e->getCode() !== 1062) {
                throw $e;
            }
        }
    }
    fclose($handle);
} else {
    echo "Error opening the file.";
}

// Close connection
$conn->close();
