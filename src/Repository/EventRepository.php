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
        float $homePct,
        float $drawPct,
        float $visitorPct,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
        ?float $odd1 = null,
        ?float $oddX = null,
        ?float $odd2 = null,
        ?float $avgGoals = null,
        ?int $predHomeGoals = null,
        ?int $predVisitorGoals = null,
    ): Event {
        $event = new Event();
        $event->setTipsterId($tipsterId);
        $event->setDate($date);
        $event->setTime($time);
        $event->setHomeTeam($homeTeam);
        $event->setVisitorTeam($visitorTeam);
        $event->setHomePct($homePct);
        $event->setDrawPct($drawPct);
        $event->setVisitorPct($visitorPct);
        $event->setHomeGoals($homeGoals);
        $event->setVisitorGoals($visitorGoals);
        $event->setOdd1($odd1);
        $event->setOddX($oddX);
        $event->setOdd2($odd2);
        $event->setAvgGoals($avgGoals);
        $event->setPredHomeGoals($predHomeGoals);
        $event->setPredVisitorGoals($predVisitorGoals);

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();

        return $event;
    }

    public function update(
        Event $event,
        float $homePct,
        float $drawPct,
        float $visitorPct,
        ?int $homeGoals,
        ?int $visitorGoals,
        ?float $odd1,
        ?float $oddX,
        ?float $odd2,
        ?float $avgGoals,
        ?int $predHomeGoals,
        ?int $predVisitorGoals,
    ): void {
        $event->setHomePct($homePct);
        $event->setDrawPct($drawPct);
        $event->setVisitorPct($visitorPct);
        $event->setHomeGoals($homeGoals);
        $event->setVisitorGoals($visitorGoals);
        $event->setOdd1($odd1);
        $event->setOddX($oddX);
        $event->setOdd2($odd2);
        $event->setAvgGoals($avgGoals);
        $event->setPredHomeGoals($predHomeGoals);
        $event->setPredVisitorGoals($predVisitorGoals);

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }

    public function createOrUpdate(
        int $tipsterId,
        string $date,
        string $time,
        string $homeTeam,
        string $visitorTeam,
        float $homePct,
        float $drawPct,
        float $visitorPct,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
        ?float $odd1 = null,
        ?float $oddX = null,
        ?float $odd2 = null,
        ?float $avgGoals = null,
        ?int $predHomeGoals = null,
        ?int $predVisitorGoals = null,
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
                $homePct,
                $drawPct,
                $visitorPct,
                $homeGoals,
                $visitorGoals,
                $odd1,
                $oddX,
                $odd2,
                $avgGoals,
                $predHomeGoals,
                $predVisitorGoals,
            );
        }

        echo "Updating event [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";

        $this->update(
            $event,
            $homePct,
            $drawPct,
            $visitorPct,
            $homeGoals,
            $visitorGoals,
            $odd1,
            $oddX,
            $odd2,
            $avgGoals,
            $predHomeGoals,
            $predVisitorGoals,
        );

        return $event;
    }

    public function removePastWithoutGoals(): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "DELETE FROM event WHERE (home_goals IS NULL OR visitor_goals IS NULL) AND date < CURDATE()";
        $conn->executeQuery($sql);
    }
}
