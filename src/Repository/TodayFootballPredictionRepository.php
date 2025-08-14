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
        $match = new ForebetMatch();
        $match->setDate($date);
        $match->setTime($time);
        $match->setHomeTeam($homeTeam);
        $match->setVisitorTeam($visitorTeam);
        $match->setHomePct($homePct);
        $match->setDrawPct($drawPct);
        $match->setVisitorPct($visitorPct);
        $match->setHomeGoals($homeGoals);
        $match->setVisitorGoals($visitorGoals);
        $match->setOdd1($odd1);
        $match->setOddX($oddX);
        $match->setOdd2($odd2);
        $match->setAvgGoals($avgGoals);
        $match->setPredHomeGoals($predHomeGoals);
        $match->setPredVisitorGoals($predVisitorGoals);
        $match->setHomeStake($match->calculateHomeStake());
        $match->setVisitorStake($match->calculateVisitorStake());
        $match->setInitialOdd1($odd1);
        $match->setInitialOddx($oddX);
        $match->setInitialOdd2($odd2);
        $match->setInitialHomePct($homePct);
        $match->setInitialVisitorPct($visitorPct);

        $this->getEntityManager()->persist($match);
        $this->getEntityManager()->flush();

        return $match;
    }

    public function update(
        ForebetMatch $match,
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
        $match->setHomePct($homePct);
        $match->setDrawPct($drawPct);
        $match->setVisitorPct($visitorPct);
        $match->setHomeGoals($homeGoals);
        $match->setVisitorGoals($visitorGoals);
        $match->setOdd1($odd1);
        $match->setOddX($oddX);
        $match->setOdd2($odd2);
        $match->setAvgGoals($avgGoals);
        $match->setPredHomeGoals($predHomeGoals);
        $match->setPredVisitorGoals($predVisitorGoals);
        $match->setHomeStake($match->calculateHomeStake());
        $match->setVisitorStake($match->calculateVisitorStake());

        $this->getEntityManager()->persist($match);
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
        $match = $this->findOneBy([
            'date' => $date,
            'homeTeam' => $homeTeam,
            'visitorTeam' => $visitorTeam,
        ]);

        if ($match === null) {
            echo "Creating match [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";

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

        echo "Updating match [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";

        $this->update(
            $match,
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

        return $match;
    }
}
