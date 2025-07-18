<?php

namespace App\Repository;

use App\Entity\Prediction;
use App\Tipster\Zulu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Prediction::class);
    }

    public function predictionsSummary(array $predictions): array {
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

        foreach ($predictions as $prediction) {
            $totalEvents++;

            if ($prediction['prediction'] === "1") {
                $totalHomePredictions++;
                $totalHomeOrDrawPredictions++;

                if ($prediction['eventHomeGoals'] > $prediction['eventVisitorGoals']) {
                    $totalHomePredictionsPositive++;
                    $totalHomeGains += $prediction['odd_1'];
                    $totalHomeOrDrawPredictionsPositive++;
                    $totalHomeOrDrawGains += $prediction['odd_1x'];
                } elseif ($prediction['eventHomeGoals'] === $prediction['eventVisitorGoals']) {
                    $totalHomeOrDrawPredictionsPositive++;
                    $totalHomeOrDrawGains += $prediction['odd_1x'];
                }
            } elseif ($prediction['prediction'] === "2") {
                $totalVisitorPredictions++;
                $totalDrawOrVisitorPredictions++;

                if ($prediction['eventHomeGoals'] < $prediction['eventVisitorGoals']) {
                    $totalVisitorPredictionsPositive++;
                    $totalVisitorGains += $prediction['odd_2'];
                    $totalDrawOrVisitorPredictionsPositive++;
                    $totalDrawOrVisitorGains += $prediction['odd_x2'];
                } elseif ($prediction['eventHomeGoals'] === $prediction['eventVisitorGoals']) {
                    $totalDrawOrVisitorPredictionsPositive++;
                    $totalDrawOrVisitorGains += $prediction['odd_x2'];
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
            (SELECT e.home_goals AS eventHomeGoals, e.visitor_goals AS eventVisitorGoals, e.odd_1, e.odd_x, e.odd_2, (1/((1/e.odd_1)+(1/e.odd_x))) AS odd_1x, (1/((1/e.odd_x)+(1/e.odd_2))) AS odd_x2, p.home_pct, p.draw_pct, p.visitor_pct,
            IF(home_pct > draw_pct AND home_pct > visitor_pct, '1', IF(draw_pct > home_pct AND draw_pct > visitor_pct, 'X', '2')) AS prediction
            FROM prediction p
            JOIN event e ON e.id = p.event_id
            WHERE e.tipster_id = :tipsterId AND e.home_goals IS NOT NULL AND e.visitor_goals IS NOT NULL) SQ
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
            (SELECT p.*, e.date, e.time, e.home_team, e.visitor_team, e.odd_1, e.odd_x, e.odd_2,
            IF(home_pct > draw_pct AND home_pct > visitor_pct, '1', IF(draw_pct > home_pct AND draw_pct > visitor_pct, 'X', '2')) AS prediction
            FROM prediction p
            LEFT JOIN event e ON e.id = p.event_id
            WHERE e.home_goals IS NULL 
              AND e.tipster_id = :tipsterId 
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
