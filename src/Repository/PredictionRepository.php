<?php

namespace App\Repository;

use App\Entity\Prediction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PredictionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Prediction::class);
    }

    public function create(
        int $eventId,
        float $homePct,
        float $drawPct,
        float $visitorPct,
        ?float $avgGoals = null,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
    ): Prediction {
        $prediction = new Prediction();
        $prediction->setEventId($eventId);
        $prediction->setHomePct($homePct);
        $prediction->setDrawPct($drawPct);
        $prediction->setVisitorPct($visitorPct);
        $prediction->setAvgGoals($avgGoals);
        $prediction->setHomeGoals($homeGoals);
        $prediction->setVisitorGoals($visitorGoals);

        $this->getEntityManager()->persist($prediction);
        $this->getEntityManager()->flush();

        return $prediction;
    }

    public function update(
        Prediction $prediction,
        float $homePct,
        float $drawPct,
        float $visitorPct,
        ?float $avgGoals,
        ?int $homeGoals,
        ?int $visitorGoals,
    ): void {
        $prediction->setHomePct($homePct);
        $prediction->setDrawPct($drawPct);
        $prediction->setVisitorPct($visitorPct);
        $prediction->setAvgGoals($avgGoals);
        $prediction->setHomeGoals($homeGoals);
        $prediction->setVisitorGoals($visitorGoals);

        $this->getEntityManager()->persist($prediction);
        $this->getEntityManager()->flush();
    }

    public function createOrUpdate(
        int $eventId,
        float $homePct,
        float $drawPct,
        float $visitorPct,
        ?float $avgGoals = null,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
    ): Prediction {
        $prediction = $this->findOneBy(['eventId' => $eventId]);

        if ($prediction === null) {
            return $this->create(
                $eventId,
                $homePct,
                $drawPct,
                $visitorPct,
                $avgGoals,
                $homeGoals,
                $visitorGoals
            );
        }

        $this->update(
            $prediction,
            $homePct,
            $drawPct,
            $visitorPct,
            $avgGoals,
            $homeGoals,
            $visitorGoals
        );

        return $prediction;
    }
}
