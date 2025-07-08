<?php

namespace App\Repository;

use App\Entity\Prediction;
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

    public function predictionsSummaryByTipster(int $tipsterId, int $pctThreshold): array
    {
        $summary = [];
        $tipsterName = '';
        $totalEvents = 0;
        $totalHomePredictions = 0;
        $totalHomePredictionsPositive = 0;
        $totalDrawPredictions = 0;
        $totalDrawPredictionsPositive = 0;
        $totalVisitorPredictions = 0;
        $totalVisitorPredictionsPositive = 0;

        $predictions = $this->getPredictionsWithEventData($tipsterId, $pctThreshold);

        foreach ($predictions as $prediction) {
            $tipsterName = $prediction['tipsterName'];
            $totalEvents++;

            $predictionTeam = $this->getPredictionTeam(
                $prediction['home_pct'],
                $prediction['draw_pct'],
                $prediction['visitor_pct']
            );

            if ($predictionTeam === "1") {
                $totalHomePredictions++;

                if ($prediction['eventHomeGoals'] > $prediction['eventVisitorGoals']) {
                    $totalHomePredictionsPositive++;
                }
            } elseif ($predictionTeam === "X") {
                $totalDrawPredictions++;

                if ($prediction['eventHomeGoals'] === $prediction['eventVisitorGoals']) {
                    $totalDrawPredictionsPositive++;
                }
            } elseif ($predictionTeam === "2") {
                $totalVisitorPredictions++;

                if ($prediction['eventHomeGoals'] < $prediction['eventVisitorGoals']) {
                    $totalVisitorPredictionsPositive++;
                }
            }
        }

        $summary['tipsterName'] = $tipsterName;
        $summary['totalEvents'] = $totalEvents;
        $summary['totalHomePredictions'] = $totalHomePredictions;
        $summary['totalHomePredictionsPositive'] = $totalHomePredictionsPositive;
        $summary['totalDrawPredictions'] = $totalDrawPredictions;
        $summary['totalDrawPredictionsPositive'] = $totalDrawPredictionsPositive;
        $summary['totalVisitorPredictions'] = $totalVisitorPredictions;
        $summary['totalVisitorPredictionsPositive'] = $totalVisitorPredictionsPositive;

        if (($totalEvents - $totalHomePredictions - $totalDrawPredictions - $totalVisitorPredictions) !== 0) {
            throw new Exception('Invalid number of events');
        }

        return $summary;
    }

    private function getPredictionsWithEventData(int $tipsterId, int $pctThreshold): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT t.name AS tipsterName, e.home_goals AS eventHomeGoals, e.visitor_goals AS eventVisitorGoals, p.* 
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
}
