<?php

namespace App\Command\Forebet;

use App\Entity\ForebetOptimaValues;
use App\Repository\ForebetOptimalValuesRepository;
use App\Repository\ForebetRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forebet:tips')]
class Tips extends Command
{
    private ForebetOptimaValues $optimalValues;

    public function __construct(
        private readonly ForebetRepository $forebetRepository,
        private readonly ForebetOptimalValuesRepository $forebetOptimalValuesRepository,
    ){
        $this->optimalValues = $this->forebetOptimalValuesRepository->get();
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $matches = $this->forebetRepository->matchesForTips();

        foreach ($matches as $match) {
            if (!$this->isValidTip($match)) {
                continue;
            }

            echo $match['date'] . " " . $match['time'] . ", ". $match['home_team'] . " - " . $match['visitor_team'] . " => ";

            if ($this->isValidHomeTip($match)) {
                $bet = ($match['bet_1'] == null)
                    ? "-------------------------> MISSING BET"
                    : $match['bet_1'];

                echo " Home pct: $match[home_pct], ";
                echo " Odd: $match[odd_1], ";
                echo " Stake: " . $this->forebetRepository->calculateStake($match['home_pct']) . ", ";
                echo " Bet: $bet \n";
            }

            if ($this->isValidVisitorTip($match,)) {
                $bet = ($match['bet_2'] == null)
                    ? "-------------------------> MISSING BET"
                    : $match['bet_2'];

                echo " Visitor pct: $match[visitor_pct], ";
                echo " Odd: $match[odd_2], ";
                echo " Stake: " . $this->forebetRepository->calculateStake($match['visitor_pct']) . ", ";
                echo " Bet: $bet \n";
            }

            echo "\n";
        }

        echo "----------------------------------------------------------------------------------\n";
        echo "SQLs \n";
        echo "----------------------------------------------------------------------------------\n\n";

        foreach ($matches as $match) {
            $updateFields = "";

            if (!$this->isValidTip($match)) {
                continue;
            }

            if ($this->isValidHomeTip($match)) {
                $homeStake = $this->forebetRepository->calculateStake($match['home_pct']);
                $updateFields .= "bet_1 = $match[odd_1], home_stake = $homeStake";
            }

            if ($this->isValidVisitorTip($match)) {
                $visitorStake = $this->forebetRepository->calculateStake($match['visitor_pct']);
                $updateFields .= "bet_2 = $match[odd_2], visitor_stake = $visitorStake";
            }

            echo "-- $match[home_team] - $match[visitor_team] \n";
            echo "UPDATE forebet_matches SET $updateFields WHERE id = $match[id];\n\n";
        }

        echo "\n";
        return Command::SUCCESS;
    }

    private function isValidTip(array $match): bool
    {
        return $this->isValidHomeTip($match) || $this->isValidVisitorTip($match);
    }

    private function isValidHomeTip(array $match): bool
    {
        return $match['prediction'] === '1'
            && $match['home_pct'] >= $this->optimalValues->getHomeMinPct()
            && $match['odd_1'] >= $this->optimalValues->getHomeMinOdd()
            && $match['odd_1'] <= $this->optimalValues->getHomeMaxOdd();
    }

    private function isValidVisitorTip(array $match): bool
    {
        return $match['prediction'] === '2'
            && $match['visitor_pct'] >= $this->optimalValues->getVisitorMinPct()
            && $match['odd_2'] >= $this->optimalValues->getVisitorMinOdd()
            && $match['odd_2'] <= $this->optimalValues->getVisitorMaxOdd();
    }
}

