<?php

include 'lib/MatchCollector.php';
include 'lib/Zulu.php';
include 'lib/ForeBet.php';

// Zulu matches
$zulu = new Zulu();
$zuluFilename = 'csv/zulu-matches.csv';
saveCsvFile($zuluFilename, $zulu->getMatches());

// ForeBet files
$foreBet = new Forebet();
$foreBetFilename = 'csv/forebet-matches.csv';
saveCsvFile($foreBetFilename, $foreBet->getMatches());

// All matches
$matchCollector = new MatchCollector();
$allMatchesFilename = 'csv/all-matches.csv';
saveCsvFile($allMatchesFilename, $matchCollector->getMatches());

// Ftp files
//ftpFiles([$zuluFilename, $foreBetFilename, $allMatchesFilename]);

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

// Functions
function saveCsvFile(string $filename, array $data): void
{
    $fp = fopen($filename, 'w');

    foreach ($data as $match) {
        fputcsv($fp, $match);
    }

    fclose($fp);
}

/**
 * @throws Exception
 */
function ftpFiles(array $filenames): void
{
    $ftp = ftp_connect('82.194.68.94');
    ftp_login($ftp, 'user-10552175', 'hfedVc0j8$V?Jtc2');

    foreach ($filenames as $filename) {
        if (!ftp_put($ftp, 'httpdocs/' . $filename, $filename, FTP_ASCII)) {
            throw new Exception('Could not ftp put file: ' . $filename);
        }
    }

    ftp_close($ftp);
}