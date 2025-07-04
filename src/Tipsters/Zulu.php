<?php

namespace App\Tipsters;

use App\Entity\Event;
use App\Entity\Prediction;
use App\Repository\EventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Zulu {
    private const TIPSTER_ID = 1;
    private const WINNING_PCT_THRESHOLD = 50;
    private const URL = 'https://es.zulubet.com';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRepository $eventRepository
    ) {
    }

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
            if ($match['homePct'] < self::WINNING_PCT_THRESHOLD && $match['visitorPct'] < self::WINNING_PCT_THRESHOLD) {
                continue;
            }

            $dateParts = explode(",", $match['date']);
            $dateParts = explode("-", $dateParts[0]);
            $date = date("Y")."-$dateParts[1]-$dateParts[0]";

            $event = $this->eventRepository->getByDateAndTeams($date, $match['homeTeam'], $match['visitorTeam']);

            if ($event === null) {
                $event = new Event();
                $event->setDate($date);
                $event->setHomeTeam($match['homeTeam']);
                $event->setVisitorTeam($match['visitorTeam']);
                $this->entityManager->persist($event);
                $this->entityManager->flush();
            }

            $prediction = new Prediction();
            $prediction->setEventId($event->getId());
            $prediction->setTipsterId(self::TIPSTER_ID);
            $prediction->setHomePct($match['homePct']);
            $prediction->setDrawPct($match['drawPct']);
            $prediction->setVisitorPct($match['visitorPct']);
            $this->entityManager->persist($prediction);
            $this->entityManager->flush();
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
