<?php

namespace TheSystem\Tipsters;

use DateTime;
use DOMDocument;
use DOMNode;
use DOMXPath;
use mysqli;
use TheSystem\Entities\Event;
use TheSystem\Entities\Prediction;
use TheSystem\Repositories\EventRepository;
use TheSystem\Utils\Db;

class Zulu {
    private const TIPSTER_ID = 1;
    private const WINNING_PCT_THRESHOLD = 50;

    private string $url = 'https://es.zulubet.com';

    public function __construct(
        private readonly EventRepository $eventRepository = new EventRepository(),
        private ?mysqli $conn = null
    ) {
        $this->conn = (new Db())->connect();
    }

    public function getMatches(): array
    {
        $html = file_get_contents($this->url);
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

            $teams = explode("-", mb_convert_encoding(
                mb_convert_encoding($row->childNodes[1]->nodeValue, 'ISO-8859-1', 'UTF-8'),
                'UTF-8',
                'auto'
            ));

            $newMatch[] = 'ZULU';
            $newMatch['date'] = $date;
            $newMatch['teams'] = $row->childNodes[1]->nodeValue;
            $newMatch['homeTeam'] = trim($teams[0]);
            $newMatch['visitorTeam'] = trim($teams[1]);
            $newMatch['homePct'] = str_replace("%", "", $row->childNodes[3]->nodeValue);
            $newMatch['drawPct'] = str_replace("%", "", $row->childNodes[4]->nodeValue);
            $newMatch['visitorPct'] = str_replace("%", "", $row->childNodes[5]->nodeValue);
            $newMatch[] = $row->childNodes[7]->nodeValue;

            $zuluMatches[] = $newMatch;
        }

        return $zuluMatches;
    }

    public function importMatches(): void
    {
        foreach ($this->getMatches() as $match) {
            if ($match['homePct'] < self::WINNING_PCT_THRESHOLD) {
                continue;
            }

            $dateParts = explode(",", $match['date']);
            $dateParts = explode("-", $dateParts[0]);
            $date = date("Y")."-$dateParts[1]-$dateParts[0]";

            $events = $this->eventRepository->getByDate($date);
            $eventId = 0;

            while ($dbRow = $events->fetch_assoc()) {
                if ($date === $dbRow['date'] && $match['homeTeam'] === $dbRow['homeTeam'] && $match['visitorTeam'] === $dbRow['visitorTeam']) {
                    $eventId = $dbRow['id'];
                }
            }

            if ($eventId === 0) {
                $event = new Event($this->conn);
                $event->date = $date;
                $event->homeTeam = $match['homeTeam'];
                $event->visitorTeam = $match['visitorTeam'];
                $eventId = $event->insert();
            }

            $prediction = new Prediction();
            $prediction->eventId = $eventId;
            $prediction->tipsterId = self::TIPSTER_ID;
            $prediction->homePct = $match['homePct'];
            $prediction->drawPct = $match['drawPct'];
            $prediction->visitorPct = $match['visitorPct'];
            $prediction->insert();
        }
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
}
