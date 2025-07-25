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
        $totalHomeGains = 0;

        $totalVisitorPredictions = 0;
        $totalVisitorPredictionsPositive = 0;
        $totalVisitorGains = 0;

        foreach ($events as $event) {
            $totalEvents++;
            $isHomeWin = $event['home_goals'] > $event['visitor_goals'];
            $isVisitorWin = $event['home_goals'] < $event['visitor_goals'];

            if ($event['prediction'] === "1") {
                $totalHomePredictions++;

                if ($isHomeWin) {
                    $totalHomePredictionsPositive++;
                    $totalHomeGains += $event['odd_1'];
                }
            } elseif ($event['prediction'] === "2") {
                $totalVisitorPredictions++;

                if ($isVisitorWin) {
                    $totalVisitorPredictionsPositive++;
                    $totalVisitorGains += $event['odd_2'];
                }
            }
        }

        $summary['totalEvents'] = $totalEvents;

        $summary['totalHomePredictions'] = $totalHomePredictions;
        $summary['totalHomePredictionsPositive'] = $totalHomePredictionsPositive;
        $summary['totalHomeGains'] = $totalHomeGains;

        $summary['totalVisitorPredictions'] = $totalVisitorPredictions;
        $summary['totalVisitorPredictionsPositive'] = $totalVisitorPredictionsPositive;
        $summary['totalVisitorGains'] = $totalVisitorGains;

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
}
