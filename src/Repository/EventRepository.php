<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Event::class);
        $this->managerRegistry = $managerRegistry;
    }

    public function create(
        string $date,
        string $homeTeam,
        string $visitorTeam,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
        ?float $odd1 = null,
        ?float $oddX = null,
        ?float $odd2 = null,
    ): ?Event {
        try {
            $event = new Event();
            $event->setDate($date);
            $event->setHomeTeam($homeTeam);
            $event->setVisitorTeam($visitorTeam);

            if ($homeGoals !== null) {
                $event->setHomeGoals($homeGoals);
            }

            if ($visitorGoals !== null) {
                $event->setVisitorGoals($visitorGoals);
            }

            if ($odd1 !== null) {
                $event->setOdd1($odd1);
            }

            if ($oddX !== null) {
                $event->setOddX($oddX);
            }

            if ($odd2 !== null) {
                $event->setOdd2($odd2);
            }

            $this->getEntityManager()->persist($event);
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException) {
            $this->managerRegistry->resetManager();
            return null;
        }

        return $event;
    }

    public function updateGoals(
        Event $event,
        ?int $homeGoals,
        ?int $visitorGoals
    ): void {
        if ($homeGoals === null || $visitorGoals === null) {
            return;
        }

        $event->setHomeGoals($homeGoals);
        $event->setVisitorGoals($visitorGoals);
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }
}
