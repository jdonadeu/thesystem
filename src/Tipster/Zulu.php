<?php

namespace App\Tipster;

use App\Repository\EventRepository;
use App\Service\FilesystemService;
use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Zulu
{
    public const TIPSTER_ID = 1;
    public const TIPSTER_NAME = 'ZULU';
    public const MIN_PCT = 50;
    private const URL = 'https://es.zulubet.com';
    private const IMPORT_FILE = 'csv/import-zulu.csv';

    public function __construct(
        protected readonly EventRepository $eventRepository,
        protected readonly FilesystemService $filesystemService,
    ) {
    }

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
            $row = $tableWithMatches->childNodes[$i];

            if (count($row->childNodes) < 14) {
                continue;
            }

            $homePct = str_replace("%", "", $row->childNodes[3]->nodeValue);
            $drawPct = str_replace("%", "", $row->childNodes[4]->nodeValue);
            $visitorPct = str_replace("%", "", $row->childNodes[5]->nodeValue);

            if ($homePct < self::MIN_PCT && $visitorPct < self::MIN_PCT) {
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

            // Teams
            $teams = explode("-", mb_convert_encoding(
                mb_convert_encoding($row->childNodes[1]->nodeValue, 'ISO-8859-1', 'UTF-8'),
                'UTF-8',
                'auto'
            ));

            $goals = explode(":", $row->childNodes[12]->nodeValue);

            $newMatch = [];
            $newMatch['date'] = $utcDate->format('Y-m-d');
            $newMatch['time'] = $utcDate->format('H:i');
            $newMatch['homeTeam'] = trim($teams[0]);
            $newMatch['visitorTeam'] = trim($teams[1]);
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

    public function persistMatches(): void
    {
        if (!($handle = fopen(self::IMPORT_FILE, 'r'))) {
            echo "Could not open file " . self::IMPORT_FILE;
            return;
        }

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $date = $row[0];
            $time = $row[1];
            $homeTeam = $row[2];
            $visitorTeam = $row[3];
            $homePct = $row[4];
            $drawPct = $row[5];
            $visitorPct = $row[6];
            $odd1 = $row[7];
            $oddX = $row[8];
            $odd2 = $row[9];
            $homeGoals = is_numeric($row[10]) ? $row[10] : null;
            $visitorGoals = is_numeric($row[11]) ? $row[11] : null;

            $event = $this->eventRepository->createOrUpdate(
                self::TIPSTER_ID,
                $date,
                $time,
                $homeTeam,
                $visitorTeam,
                $homePct,
                $drawPct,
                $visitorPct,
                $homeGoals,
                $visitorGoals,
                $odd1,
                $oddX,
                $odd2,
            );
        }

        fclose($handle);
    }
}
