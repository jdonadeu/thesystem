<?php

namespace App\Command\Forebet;

use App\Repository\ForebetRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forebet:tips')]
class Tips extends Command
{
    private const OPTIMAL_VALUES = [
        'HOME_MIN_PCT' => 43,
        'HOME_MIN_ODD' => 2.85,
        'HOME_MAX_ODD' => 99,
        'VISITOR_MIN_PCT' => 57,
        'VISITOR_MIN_ODD' => 2.2,
        'VISITOR_MAX_ODD' => 99,
    ];

    public function __construct(private readonly ForebetRepository $forebetRepository)
    {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $matches = $this->forebetRepository->matchesForTips();

        echo "\n";
        echo "----------------------------------------------------------------------------------\n";
        echo "Optimal values => ";
        echo "Home min pct: " . self::OPTIMAL_VALUES['HOME_MIN_PCT'] . " / ";
        echo "Home min odd: " . self::OPTIMAL_VALUES['HOME_MIN_ODD'] . " / ";
        echo "Home max odd: " . self::OPTIMAL_VALUES['HOME_MAX_ODD'] . " / ";
        echo "Visitor min pct: " . self::OPTIMAL_VALUES['VISITOR_MIN_PCT'] . " / ";
        echo "Visitor min odd: " . self::OPTIMAL_VALUES['VISITOR_MIN_ODD'] . " / ";
        echo "Visitor max odd: " . self::OPTIMAL_VALUES['VISITOR_MAX_ODD']. "\n";
        echo "----------------------------------------------------------------------------------\n\n";

        foreach ($matches as $match) {
            // Only play home for now
            if ($match['prediction'] !== "1") {
                continue;
            }

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
                echo " Stake: $match[home_stake], ";
                echo " Bet: $bet \n";
            }
            if ($this->isValidVisitorTip($match,)) {
                $bet = ($match['bet_2'] == null)
                    ? "-------------------------> MISSING BET"
                    : $match['bet_2'];

                echo " Visitor pct: $match[visitor_pct], ";
                echo " Odd: $match[odd_2], ";
                echo " Stake: $match[visitor_stake], ";
                echo " Bet: $bet \n";
            }

            echo "\n";
        }

        echo "----------------------------------------------------------------------------------\n";
        echo "SQLs \n";
        echo "----------------------------------------------------------------------------------\n\n";

        foreach ($matches as $match) {
            // Only play home for now
            if ($match['prediction'] !== "1") {
                continue;
            }

            $updateFields = "";

            if (!$this->isValidTip($match) || ($match['bet_1'] != null || $match['bet_2'] != null)) {
                continue;
            }

            if ($this->isValidHomeTip($match)) {
                $updateFields .= "bet_1 = $match[odd_1], home_stake = $match[home_stake]";
            }
            if ($this->isValidVisitorTip($match)) {
                $updateFields .= "bet_2 = $match[odd_2], visitor_stake = $match[visitor_stake]";
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
            && $match['home_pct'] >= self::OPTIMAL_VALUES['HOME_MIN_PCT']
            && $match['odd_1'] >= self::OPTIMAL_VALUES['HOME_MIN_ODD']
            && $match['odd_1'] <= self::OPTIMAL_VALUES['HOME_MAX_ODD'];
    }

    private function isValidVisitorTip(array $match): bool
    {
        return $match['prediction'] === '2'
            && $match['visitor_pct'] >= self::OPTIMAL_VALUES['VISITOR_MIN_PCT']
            && $match['odd_2'] >= self::OPTIMAL_VALUES['VISITOR_MIN_ODD']
            && $match['odd_2'] <= self::OPTIMAL_VALUES['VISITOR_MAX_ODD'];
    }
}

