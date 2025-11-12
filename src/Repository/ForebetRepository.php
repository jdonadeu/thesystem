<?php

namespace App\Repository;

use App\Entity\ForebetMatch;
use App\Tipster\ForeBet;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class ForebetRepository extends ServiceEntityRepository
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
        $match->setInitialOdd1($odd1);
        $match->setInitialOddx($oddX);
        $match->setInitialOdd2($odd2);
        $match->setInitialHomePct($homePct);
        $match->setInitialVisitorPct($visitorPct);
        $match->setCreatedAt(new DateTime('now'));

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
        $match->setUpdatedAt(new DateTime('now'));

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
        /** @var ForebetMatch $match */
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

    public function matchesSummary(array $matches): array {
        $summary = [];
        $totalMatches = 0;

        $totalHomePredictions = 0;
        $totalHomePredictionsPositive = 0;
        $totalHomeStakes = 0;
        $totalHomeGains = 0;

        $totalVisitorPredictions = 0;
        $totalVisitorPredictionsPositive = 0;
        $totalVisitorStakes = 0;
        $totalVisitorGains = 0;

        foreach ($matches as $match) {
            $totalMatches++;
            $isHomeWin = $match['home_goals'] > $match['visitor_goals'];
            $isVisitorWin = $match['home_goals'] < $match['visitor_goals'];

            if ($match['prediction'] === "1") {
                $totalHomePredictions++;
                $homeStake = $this->calculateStake($match['home_pct']);
                $totalHomeStakes += $homeStake;

                if ($isHomeWin) {
                    $totalHomePredictionsPositive++;
                    $totalHomeGains += $homeStake * $match['odd_1'];
                }
            } elseif ($match['prediction'] === "2") {
                $totalVisitorPredictions++;
                $visitorStake = $this->calculateStake($match['visitor_pct']);
                $totalVisitorStakes += $visitorStake;

                if ($isVisitorWin) {
                    $totalVisitorPredictionsPositive++;
                    $totalVisitorGains += $visitorStake * $match['odd_2'];
                }
            }
        }

        $summary['matches'] = $totalMatches;

        $summary['homePredictions'] = $totalHomePredictions;
        $summary['homePredictionsPositive'] = $totalHomePredictionsPositive;
        $summary['homeStakes'] = $totalHomeStakes;
        $summary['homeGains'] = $totalHomeGains;
        $summary['homeNetGains'] = $totalHomeGains - $totalHomeStakes;

        $summary['visitorPredictions'] = $totalVisitorPredictions;
        $summary['visitorPredictionsPositive'] = $totalVisitorPredictionsPositive;
        $summary['visitorStakes'] = $totalVisitorStakes;
        $summary['visitorGains'] = $totalVisitorGains;
        $summary['visitorNetGains'] = $totalVisitorGains - $totalVisitorStakes;

        if (($totalMatches - $totalHomePredictions - $totalVisitorPredictions) !== 0) {
            throw new Exception('Invalid number of matches');
        }

        return $summary;
    }

    public function getMatchesForSummary(int $minPct, float $minOdd, float $maxOdd, int $lastMonths): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT *
            FROM forebet_matches_extended
            WHERE home_goals IS NOT NULL AND visitor_goals IS NOT NULL
            AND date >= DATE_SUB(CURDATE(), INTERVAL $lastMonths MONTH)
            AND (
            (prediction = '1' AND home_pct >= $minPct AND odd_1 >= $minOdd AND odd_1 <= $maxOdd) 
            OR (prediction = '2' AND visitor_pct >= $minPct AND odd_2 >= $minOdd AND odd_2 <= $maxOdd)
            )
            ";

        $queryResults = $conn->executeQuery($sql);
        $matches = $queryResults->fetchAllAssociative();

        return $matches;
    }

    public function matchesForTips(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT * 
            FROM forebet_matches_extended
            WHERE home_goals IS NULL 
            ORDER BY date, time
            ";

        $resultSet = $conn->executeQuery($sql);
        return $resultSet->fetchAllAssociative();
    }

    public function calculateStake(float $pct): float
    {
        return 1;
        return 1 + ($pct - ForeBet::MIN_PCT) * 0.05;
    }
}
