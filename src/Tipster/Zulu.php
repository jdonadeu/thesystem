<?php

namespace App\Tipster;

use DateTime;
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

    public function getMatches(): array
    {
        $html = file_get_contents(self::URL);
        $table = $this->getTableWithClass($html, 'main_table');
        $tableWithMatches = $this->getTableWithClass($this->getInnerHtml($table), 'content_table');

        $zuluMatches = [];
        $now = new DateTime();

        for ($i = 2; $i < count($tableWithMatches->childNodes); $i++) {
            $newMatch = [];
            $row = $tableWithMatches->childNodes[$i];

            if (count($row->childNodes) < 14) {
                continue;
            }

            // Date
            $date = $row->childNodes[0]->nodeValue;
            $seed = ']]>';
            $start = strpos($date, $seed) + strlen($seed);
            $date = substr($date, $start);

            if (DateTime::createFromFormat("d-m, H:i", $date) < $now) {
                continue;
            }

            $dateParts = explode(",", $date);
            $dateParts = explode("-", $dateParts[0]);
            $date = date("Y")."-$dateParts[1]-$dateParts[0]";

            $teams = explode("-", mb_convert_encoding(
                mb_convert_encoding($row->childNodes[1]->nodeValue, 'ISO-8859-1', 'UTF-8'),
                'UTF-8',
                'auto'
            ));

            $newMatch['date'] = $date;
            $newMatch['homeTeam'] = $this->teamNameMapper->getMappedTeamName(trim($teams[0]));
            $newMatch['visitorTeam'] = $this->teamNameMapper->getMappedTeamName(trim($teams[1]));
            $newMatch['homePct'] = str_replace("%", "", $row->childNodes[3]->nodeValue);
            $newMatch['drawPct'] = str_replace("%", "", $row->childNodes[4]->nodeValue);
            $newMatch['visitorPct'] = str_replace("%", "", $row->childNodes[5]->nodeValue);

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

    public function importMatches(): void
    {
        $matches = $this->getMatches();
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

            if ($homePct < self::WINNING_PCT_THRESHOLD && $visitorPct < self::WINNING_PCT_THRESHOLD) {
                continue;
            }

            $event = $this->getEvent(self::TIPSTER_NAME, $date, $homeTeam, $visitorTeam, $commit);

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
