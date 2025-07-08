<?php

namespace App\Tipster;

use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Zulu extends Tipster
{
    private const TIPSTER_ID = 1;
    private const TIPSTER_NAME = 'ZULU';
    private const WINNING_PCT_THRESHOLD = 50;
    private const URL = 'https://es.zulubet.com';
    private const IMPORT_FILE = 'csv/import-zulu.csv';

    public function getMatches(string $date): array
    {
        $dateTime = new DateTime($date);
        $urlDate = $dateTime->format('d'). "-" . $dateTime->format('m') . "-" . $dateTime->format('Y') ;
        $url = self::URL . "/pronosticos-{$urlDate}.html";

        $html = file_get_contents($url);
        $table = $this->getTableWithClass($html, 'main_table');
        $tableWithMatches = $this->getTableWithClass($this->getInnerHtml($table), 'content_table');

        $zuluMatches = [];

        for ($i = 2; $i < count($tableWithMatches->childNodes); $i++) {
            $newMatch = [];
            $row = $tableWithMatches->childNodes[$i];

            if (count($row->childNodes) < 14) {
                continue;
            }

            $homePct = str_replace("%", "", $row->childNodes[3]->nodeValue);
            $drawPct = str_replace("%", "", $row->childNodes[4]->nodeValue);
            $visitorPct = str_replace("%", "", $row->childNodes[5]->nodeValue);

            if ($homePct < self::WINNING_PCT_THRESHOLD && $visitorPct < self::WINNING_PCT_THRESHOLD) {
                continue;
            }

            // Date
            $dateTime = $row->childNodes[0]->nodeValue;

            if (preg_match("/\<\!\[CDATA\[mf_usertime\('([\d\/]+), ([\d:]+)'/", $dateTime, $matches)) {
                $date = $matches[1];
                $time = $matches[2];
            } else {
                echo "Error reading date from $dateTime \n";
                continue;
            }

            $utcDate = new DateTime("$date $time", new DateTimeZone('UTC'));
            $utcDate->setTimezone(new DateTimeZone('Europe/Madrid'));
            $date = $utcDate->format('Y-m-d');

            // Teams
            $teams = explode("-", mb_convert_encoding(
                mb_convert_encoding($row->childNodes[1]->nodeValue, 'ISO-8859-1', 'UTF-8'),
                'UTF-8',
                'auto'
            ));

            $goals = explode(":", $row->childNodes[12]->nodeValue);

            $newMatch['date'] = $date;
            $newMatch['homeTeam'] = $this->teamNameMapper->getMappedTeamName(trim($teams[0]));
            $newMatch['visitorTeam'] = $this->teamNameMapper->getMappedTeamName(trim($teams[1]));
            $newMatch['homePct'] = $homePct;
            $newMatch['drawPct'] = $drawPct;
            $newMatch['visitorPct'] = $visitorPct;
            $newMatch['odd_1'] = $row->childNodes[9]->nodeValue;
            $newMatch['odd_1x'] = $row->childNodes[10]->nodeValue;
            $newMatch['odd_2'] = $row->childNodes[11]->nodeValue;
            $newMatch['homeGoals'] = $goals[0] ?? '';
            $newMatch['visitorGoals'] = $goals[1] ?? '';

            $zuluMatches[] = $newMatch;
        }

        return $zuluMatches;
    }

    private function getTableWithClass(string $html, string $className): DOMNode
    {
        $html = preg_replace("/&(?!\S+;)/", "&amp;", $html);

        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $finder = new DomXPath($doc);
        $table = $finder->query("//*[contains(@class, '$className')]");

        return $table->item(0);
    }

    private function getInnerHtml($node): string {
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML( $child );
        }

        return $innerHTML;
    }

    public function importMatches(string $date): void
    {
        $matches = $this->getMatches($date);
        echo self::TIPSTER_NAME . ": Importing " . count($matches) . " matches\n";
        $this->filesystemService->saveCsvFile(self::IMPORT_FILE, $matches);
    }

    public function persistMatches(bool $commit): void
    {
        if (!($handle = fopen(self::IMPORT_FILE, 'r'))) {
            echo "Could not open file " . self::IMPORT_FILE;
            return;
        }

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $date = $row[0];
            $homeTeam = $row[1];
            $visitorTeam = $row[2];
            $homePct = $row[3];
            $drawPct = $row[4];
            $visitorPct = $row[5];
            $odd1 = $row[6];
            $odd1x = $row[7];
            $odd2 = $row[8];
            $homeGoals = is_numeric($row[9]) ? $row[9] : null;
            $visitorGoals = is_numeric($row[10]) ? $row[10] : null;

            $event = $this->getEvent(
                $commit,
                self::TIPSTER_NAME,
                $date,
                $homeTeam,
                $visitorTeam,
                $homeGoals,
                $visitorGoals,
                $odd1,
                $odd1x,
                $odd2,
            );

            if ($commit) {
                $this->predictionRepository->create(
                    $event->getId(),
                    self::TIPSTER_ID,
                    $homePct,
                    $drawPct,
                    $visitorPct
                );
            }
        }

        fclose($handle);
    }
}
