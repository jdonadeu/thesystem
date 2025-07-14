<?php

namespace App\Repository;

use App\Entity\Event;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Event::class);
    }

    public function create(
        int $tipsterId,
        string $date,
        string $time,
        string $homeTeam,
        string $visitorTeam,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
        ?float $odd1 = null,
        ?float $oddX = null,
        ?float $odd2 = null,
    ): Event {
        $event = new Event();
        $event->setTipsterId($tipsterId);
        $event->setDate($date);
        $event->setTime($time);
        $event->setHomeTeam($homeTeam);
        $event->setVisitorTeam($visitorTeam);
        $event->setHomeGoals($homeGoals);
        $event->setVisitorGoals($visitorGoals);
        $event->setOdd1($odd1);
        $event->setOddX($oddX);
        $event->setOdd2($odd2);

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();

        return $event;
    }

    public function update(
        Event $event,
        ?int $homeGoals,
        ?int $visitorGoals,
        ?float $odd1,
        ?float $oddX,
        ?float $odd2,
    ): void {
        $event->setHomeGoals($homeGoals);
        $event->setVisitorGoals($visitorGoals);
        $event->setOdd1($odd1);
        $event->setOddX($oddX);
        $event->setOdd2($odd2);

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }

    public function createOrUpdate(
        int $tipsterId,
        string $date,
        string $time,
        string $homeTeam,
        string $visitorTeam,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
        ?float $odd1 = null,
        ?float $oddX = null,
        ?float $odd2 = null,
    ): Event {
        $event = $this->findOneBy([
            'tipsterId' => $tipsterId,
            'date' => $date,
            'homeTeam' => $homeTeam,
            'visitorTeam' => $visitorTeam,
        ]);

        if ($event === null) {
            echo "Creating event [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";

            return $this->create(
                $tipsterId,
                $date,
                $time,
                $homeTeam,
                $visitorTeam,
                $homeGoals,
                $visitorGoals,
                $odd1,
                $oddX,
                $odd2,
            );
        }

        echo "Updating event [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";

        $this->update(
            $event,
            $homeGoals,
            $visitorGoals,
            $odd1,
            $oddX,
            $odd2,
        );

        return $event;
    }

    public function removePastWithoutGoals(): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $qb = $this->createQueryBuilder('e');
        $qb->where('e.date < :today')
            ->andWhere('e.homeGoals IS NULL')
            ->setParameter('today', new DateTime('today'));
        $events = $qb->getQuery()->getResult();

        foreach ($events as $event) {
            $eventId = $event->getId();

            $sql = "DELETE FROM prediction WHERE event_id = :eventId";
            $conn->executeQuery($sql, ['eventId' => $eventId]);

            $sql = "DELETE FROM event WHERE id = :eventId";
            $conn->executeQuery($sql, ['eventId' => $eventId]);
        }
    }
}
