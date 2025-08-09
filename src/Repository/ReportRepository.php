<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Event::class);
    }

    public function eventsSummary(array $events): array {
        $summary = [];
        $totalEvents = 0;

        $totalHomePredictions = 0;
        $totalHomePredictionsPositive = 0;
        $totalHomeStakes = 0;
        $totalHomeGains = 0;

        $totalVisitorPredictions = 0;
        $totalVisitorPredictionsPositive = 0;
        $totalVisitorStakes = 0;
        $totalVisitorGains = 0;

        foreach ($events as $event) {
            $eventObj = new Event();
            $eventObj->setHomePct($event['home_pct']);
            $eventObj->setVisitorPct($event['visitor_pct']);

            $totalEvents++;
            $isHomeWin = $event['home_goals'] > $event['visitor_goals'];
            $isVisitorWin = $event['home_goals'] < $event['visitor_goals'];

            if ($event['prediction'] === "1") {
                $totalHomePredictions++;
                $homeStake = $eventObj->calculateHomeStake();
                $totalHomeStakes += $homeStake;

                if ($isHomeWin) {
                    $totalHomePredictionsPositive++;
                    $totalHomeGains += $homeStake * $event['odd_1'];
                }
            } elseif ($event['prediction'] === "2") {
                $totalVisitorPredictions++;
                $visitorStake = $eventObj->calculateVisitorStake();
                $totalVisitorStakes += $visitorStake;

                if ($isVisitorWin) {
                    $totalVisitorPredictionsPositive++;
                    $totalVisitorGains += $visitorStake * $event['odd_2'];
                }
            }
        }

        $summary['totalEvents'] = $totalEvents;

        $summary['totalHomePredictions'] = $totalHomePredictions;
        $summary['totalHomePredictionsPositive'] = $totalHomePredictionsPositive;
        $summary['totalHomeStakes'] = $totalHomeStakes;
        $summary['totalHomeGains'] = $totalHomeGains;
        $summary['totalHomeNetGains'] = $totalHomeGains - $totalHomeStakes;

        $summary['totalVisitorPredictions'] = $totalVisitorPredictions;
        $summary['totalVisitorPredictionsPositive'] = $totalVisitorPredictionsPositive;
        $summary['totalVisitorStakes'] = $totalVisitorStakes;
        $summary['totalVisitorGains'] = $totalVisitorGains;
        $summary['totalVisitorNetGains'] = $totalVisitorGains - $totalVisitorStakes;

        if (($totalEvents - $totalHomePredictions - $totalVisitorPredictions) !== 0) {
            throw new Exception('Invalid number of events');
        }

        return $summary;
    }

    public function getEventsForSummary(int $tipsterId, int $minPct, float $minOdd, float $maxOdd,): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT * FROM
            (SELECT *,
            IF(home_pct > draw_pct AND home_pct > visitor_pct, '1', IF(draw_pct > home_pct AND draw_pct > visitor_pct, 'X', '2')) AS prediction
            FROM event
            WHERE tipster_id = :tipsterId AND home_goals IS NOT NULL AND visitor_goals IS NOT NULL) SQ
            WHERE (prediction = '1' AND home_pct >= $minPct AND odd_1 >= $minOdd AND odd_1 <= $maxOdd) 
               OR (prediction = '2' AND visitor_pct >= $minPct AND odd_2 >= $minOdd AND odd_2 <= $maxOdd)
            ";

        $resultSet = $conn->executeQuery($sql, ['tipsterId' => $tipsterId]);
        return $resultSet->fetchAllAssociative();
    }

    public function eventsForTips(int $tipsterId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT *,
            IF(home_pct > draw_pct AND home_pct > visitor_pct, '1', IF(draw_pct > home_pct AND draw_pct > visitor_pct, 'X', '2')) AS prediction
            FROM event
            WHERE home_goals IS NULL AND tipster_id = :tipsterId 
            ORDER BY date, time
            ";

        $resultSet = $conn->executeQuery(
            $sql,
            [
                'tipsterId' => $tipsterId,
            ]
        );

        return $resultSet->fetchAllAssociative();
    }

    public function placedBets(int $tipsterId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT *, (home_goals > visitor_goals) as homeWin, (home_goals = visitor_goals) as drawWin, (home_goals < visitor_goals) as visitorWin
            FROM event 
            WHERE
            tipster_id = :tipsterId
            AND home_goals IS NOT NULL
            AND (bet_1 IS NOT NULL OR bet_2 IS NOT NULL)
            ";

        $resultSet = $conn->executeQuery(
            $sql,
            [
                'tipsterId' => $tipsterId,
            ]
        );

        return $resultSet->fetchAllAssociative();
    }

    public function updateStakes(): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT *
            FROM event_extended
            WHERE (prediction = '1' AND home_pct >= 43 AND odd_1 >= 2.85  AND odd_1 <= 99)
            OR (prediction = '2' AND visitor_pct >= 57 AND odd_2 >= 2.2  AND odd_2 <= 99)
            ";

        $events = $conn->executeQuery($sql)->fetchAllAssociative();

        foreach ($events as $event) {
            $eventObj = new Event();
            $eventObj->setHomePct($event['home_pct']);
            $eventObj->setVisitorPct($event['visitor_pct']);

            $stake = ($event['prediction'] === "1")
                ? $eventObj->calculateHomeStake()
                : $eventObj->calculateVisitorStake();

            $updateSql = "
            UPDATE event
            SET stake = $stake
            WHERE id = $event[id]
            ";

            $conn->executeQuery($updateSql);
        }
    }
}
