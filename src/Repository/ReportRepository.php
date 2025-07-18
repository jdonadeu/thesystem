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

    public function predictionsSummary(array $events): array {
        $summary = [];
        $totalEvents = 0;

        $totalHomePredictions = 0;
        $totalHomePredictionsPositive = 0;
        $totalHomeGains = 0;

        $totalHomeOrDrawPredictions = 0;
        $totalHomeOrDrawPredictionsPositive = 0;
        $totalHomeOrDrawGains = 0;

        $totalVisitorPredictions = 0;
        $totalVisitorPredictionsPositive = 0;
        $totalVisitorGains = 0;

        $totalDrawOrVisitorPredictions = 0;
        $totalDrawOrVisitorPredictionsPositive = 0;
        $totalDrawOrVisitorGains = 0;

        foreach ($events as $event) {
            $totalEvents++;
            $isHomeWin = $event['home_goals'] > $event['visitor_goals'];
            $isDraw = $event['home_goals'] === $event['visitor_goals'];
            $isVisitorWin = $event['home_goals'] < $event['visitor_goals'];

            if ($event['prediction'] === "1") {
                $totalHomePredictions++;
                $totalHomeOrDrawPredictions++;

                if ($isHomeWin) {
                    $totalHomePredictionsPositive++;
                    $totalHomeGains += $event['odd_1'];
                    $totalHomeOrDrawPredictionsPositive++;
                    $totalHomeOrDrawGains += $event['odd_1x'];
                } elseif ($isDraw) {
                    $totalHomeOrDrawPredictionsPositive++;
                    $totalHomeOrDrawGains += $event['odd_1x'];
                }
            } elseif ($event['prediction'] === "2") {
                $totalVisitorPredictions++;
                $totalDrawOrVisitorPredictions++;

                if ($isVisitorWin) {
                    $totalVisitorPredictionsPositive++;
                    $totalVisitorGains += $event['odd_2'];
                    $totalDrawOrVisitorPredictionsPositive++;
                    $totalDrawOrVisitorGains += $event['odd_x2'];
                } elseif ($isDraw) {
                    $totalDrawOrVisitorPredictionsPositive++;
                    $totalDrawOrVisitorGains += $event['odd_x2'];
                }
            }
        }

        $summary['totalEvents'] = $totalEvents;

        $summary['totalHomePredictions'] = $totalHomePredictions;
        $summary['totalHomePredictionsPositive'] = $totalHomePredictionsPositive;
        $summary['totalHomeGains'] = $totalHomeGains;

        $summary['totalHomeOrDrawPredictions'] = $totalHomeOrDrawPredictions;
        $summary['totalHomeOrDrawPredictionsPositive'] = $totalHomeOrDrawPredictionsPositive;
        $summary['totalHomeOrDrawGains'] = $totalHomeOrDrawGains;

        $summary['totalVisitorPredictions'] = $totalVisitorPredictions;
        $summary['totalVisitorPredictionsPositive'] = $totalVisitorPredictionsPositive;
        $summary['totalVisitorGains'] = $totalVisitorGains;

        $summary['totalDrawOrVisitorPredictions'] = $totalDrawOrVisitorPredictions;
        $summary['totalDrawOrVisitorPredictionsPositive'] = $totalDrawOrVisitorPredictionsPositive;
        $summary['totalDrawOrVisitorGains'] = $totalDrawOrVisitorGains;

        if (($totalEvents - $totalHomePredictions - $totalVisitorPredictions) !== 0) {
            throw new Exception('Invalid number of events');
        }

        return $summary;
    }

    public function getPredictionsForSummary(
        int $tipsterId,
        int $pctThreshold,
        float $minOdd,
        float $maxOdd,
    ): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT * FROM
            (SELECT *, (1/((1/odd_1)+(1/odd_x))) AS odd_1x, (1/((1/odd_x)+(1/odd_2))) AS odd_x2,
            IF(home_pct > draw_pct AND home_pct > visitor_pct, '1', IF(draw_pct > home_pct AND draw_pct > visitor_pct, 'X', '2')) AS prediction
            FROM event
            WHERE tipster_id = :tipsterId AND home_goals IS NOT NULL AND visitor_goals IS NOT NULL) SQ
            WHERE (prediction = '1' AND home_pct >= $pctThreshold AND odd_1 >= $minOdd AND odd_1 < $maxOdd) 
               OR (prediction = '2' AND visitor_pct >= $pctThreshold AND odd_2 >= $minOdd AND odd_2 < $maxOdd)
            ";

        $resultSet = $conn->executeQuery($sql, ['tipsterId' => $tipsterId]);
        return $resultSet->fetchAllAssociative();
    }

    public function tips(int $tipsterId, int $homePct, float $odd1, int $visitorPct, float $odd2): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT * FROM
            (SELECT *,
            IF(home_pct > draw_pct AND home_pct > visitor_pct, '1', IF(draw_pct > home_pct AND draw_pct > visitor_pct, 'X', '2')) AS prediction
            FROM event
            WHERE home_goals IS NULL 
              AND tipster_id = :tipsterId 
            ORDER BY date, time) SQ
            WHERE ((prediction = '1' AND home_pct >= :homePct AND odd_1 >= :odd1) 
                       OR (prediction = '2' AND visitor_pct >= :visitorPct AND odd_2 >= :odd2))
            ";

        $resultSet = $conn->executeQuery(
            $sql,
            [
                'tipsterId' => $tipsterId,
                'homePct' => $homePct,
                'odd1' => $odd1,
                'visitorPct' => $visitorPct,
                'odd2' => $odd2,
            ]
        );

        return $resultSet->fetchAllAssociative();
    }
}
