<?php

namespace App\Repository;

use App\Entity\Prediction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class PredictionRepository extends ServiceEntityRepository
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Prediction::class);
        $this->managerRegistry = $managerRegistry;
    }

    public function create(
        int $eventId,
        int $tipsterId,
        float $homePct,
        float $drawPct,
        float $visitorPct,
        ?float $avgGoals = null,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
    ): ?Prediction {
        try {
            $prediction = new Prediction();
            $prediction->setEventId($eventId);
            $prediction->setTipsterId($tipsterId);
            $prediction->setHomePct($homePct);
            $prediction->setDrawPct($drawPct);
            $prediction->setVisitorPct($visitorPct);
            $prediction->setAvgGoals($avgGoals);
            $prediction->setHomeGoals($homeGoals);
            $prediction->setVisitorGoals($visitorGoals);
            $this->getEntityManager()->persist($prediction);
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException) {
            $this->managerRegistry->resetManager();
            return null;
        }

        return $prediction;
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
