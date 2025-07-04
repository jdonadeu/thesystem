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

    public function create(string $date, string $homeTeam, string $visitorTeam): ?Event
    {
        try {
            $event = new Event();
            $event->setDate($date);
            $event->setHomeTeam($homeTeam);
            $event->setVisitorTeam($visitorTeam);
            $this->getEntityManager()->persist($event);
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException) {
            $this->managerRegistry->resetManager();
            return null;
        }

        return $event;
    }
}
