<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getByDateAndTeams(string $date, string $homeTeam, string $visitorTeam): ?Event
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.date = :date')
            ->andWhere('e.homeTeam = :homeTeam')
            ->andWhere('e.visitorTeam = :visitorTeam')
            ->setParameter('date', $date)
            ->setParameter('homeTeam', $homeTeam)
            ->setParameter('visitorTeam', $visitorTeam);

        $query = $qb->getQuery();
        return $query->setMaxResults(1)->getOneOrNullResult();
    }
}
