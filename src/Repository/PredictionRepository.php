<?php

namespace App\Repository;

use App\Entity\Prediction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

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
}
