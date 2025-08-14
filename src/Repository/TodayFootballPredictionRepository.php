<?php

namespace App\Repository;

use App\Entity\ForebetMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TodayFootballPredictionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, ForebetMatch::class);
    }

    public function create(
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
    ): ForebetMatch {
        $event = new ForebetMatch();
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
        $event->setHomeStake($event->calculateHomeStake());
        $event->setVisitorStake($event->calculateVisitorStake());
        $event->setInitialOdd1($odd1);
        $event->setInitialOddx($oddX);
        $event->setInitialOdd2($odd2);
        $event->setInitialHomePct($homePct);
        $event->setInitialVisitorPct($visitorPct);

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();

        return $event;
    }

    public function update(
        ForebetMatch $event,
        float        $homePct,
        float        $drawPct,
        float        $visitorPct,
        ?int         $homeGoals,
        ?int         $visitorGoals,
        ?float       $odd1,
        ?float       $oddX,
        ?float       $odd2,
        ?float       $avgGoals,
        ?int         $predHomeGoals,
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
        $event->setHomeStake($event->calculateHomeStake());
        $event->setVisitorStake($event->calculateVisitorStake());

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }

    public function createOrUpdate(
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
    ): ForebetMatch {
        $event = $this->findOneBy([
            'date' => $date,
            'homeTeam' => $homeTeam,
            'visitorTeam' => $visitorTeam,
        ]);

        if ($event === null) {
            echo "Creating event [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";

            return $this->create(
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
}
