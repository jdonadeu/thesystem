<?php

namespace App\Repository;

use App\Entity\Prediction;
use App\Tipster\Zulu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class ReportRepository extends ServiceEntityRepository
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Prediction::class);
        $this->managerRegistry = $managerRegistry;
    }

    public function predictionsSummaryByTipster(int $tipsterId, int $pctThreshold, float $oddThreshold): array
    {
        $summary = [];
        $tipsterName = '';
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

        $predictions = $this->getPredictionsWithEventData($tipsterId, $pctThreshold);

        foreach ($predictions as $prediction) {
            $tipsterName = $prediction['tipsterName'];

            $predictionTeam = $this->getPredictionTeam(
                $prediction['home_pct'],
                $prediction['draw_pct'],
                $prediction['visitor_pct']
            );

            if (($predictionTeam === "1" && $prediction['odd_1'] < $oddThreshold)
                || $predictionTeam === "X"
                || ($predictionTeam === "2" && $prediction['odd_2'] < $oddThreshold)
            ) {
                continue;
            }

            $totalEvents++;

            if ($predictionTeam === "1") {
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
            } elseif ($predictionTeam === "2") {
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

        $summary['tipsterName'] = $tipsterName;
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

    private function getPredictionsWithEventData(int $tipsterId, int $pctThreshold): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT t.name AS tipsterName, e.home_goals AS eventHomeGoals, e.visitor_goals AS eventVisitorGoals, e.odd_1, e.odd_x, e.odd_2, (1/((1/e.odd_1)+(1/e.odd_x))) AS odd_1x, (1/((1/e.odd_x)+(1/e.odd_2))) AS odd_x2, p.* 
            FROM prediction p
            JOIN event e ON e.id = p.event_id
            JOIN tipster t ON t.id = p.tipster_id
            WHERE p.tipster_id = :tipsterId 
              AND e.home_goals IS NOT NULL AND e.visitor_goals IS NOT NULL  
              AND (p.home_pct >= $pctThreshold OR p.visitor_pct >= $pctThreshold)
            ";

        $resultSet = $conn->executeQuery($sql, ['tipsterId' => $tipsterId]);

        return $resultSet->fetchAllAssociative();
    }

    private function getPredictionTeam(float $homePct, float $drawPct, float $visitorPct): string
    {
        $highestPct = max($homePct, $drawPct, $visitorPct);

        if ($homePct === $highestPct) {
            return "1";
        } elseif ($drawPct === $highestPct) {
            return "X";
        } else {
            return "2";
        }
    }

    public function zuluTips(int $homePct, float $odd1, int $visitorPct, float $odd2): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT t.name AS tipsterName, e.date, e.home_team, e.visitor_team, e.odd_1, e.odd_2, p.home_pct, p.visitor_pct
            FROM prediction p
            LEFT JOIN event e ON e.id = p.event_id
            LEFT JOIN tipster t ON t.id = p.tipster_id
            WHERE e.home_goals IS NULL 
              AND p.tipster_id = :tipsterId 
              AND ((p.home_pct >= :homePct AND e.odd_1 >= :odd1) OR (p.visitor_pct >= :visitorPct AND e.odd_2 >= :odd2))
            ";

        $resultSet = $conn->executeQuery(
            $sql,
            [
                'tipsterId' => Zulu::TIPSTER_ID,
                'homePct' => $homePct,
                'odd1' => $odd1,
                'visitorPct' => $visitorPct,
                'odd2' => $odd2,
            ]
        );

        return $resultSet->fetchAllAssociative();
    }
}
