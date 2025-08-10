<?php

namespace App\Command;

use App\Repository\ReportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tipster:tips')]
class TipsterTips extends Command
{
    private const OPTIMAL_VALUES = [
        2 => [
            'HOME_MIN_PCT' => 43,
            'HOME_MIN_ODD' => 2.85,
            'HOME_MAX_ODD' => 99,
            'VISITOR_MIN_PCT' => 57,
            'VISITOR_MIN_ODD' => 2.2,
            'VISITOR_MAX_ODD' => 99,
        ]
    ];

    public function __construct(private readonly ReportRepository $reportRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tipsterId', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = $input->getArgument('tipsterId');
        $events = $this->reportRepository->eventsForTips($tipsterId);

        echo "\n";
        echo "----------------------------------------------------------------------------------\n";
        echo "Optimal values => ";
        echo "Home min pct: " . self::OPTIMAL_VALUES[$tipsterId]['HOME_MIN_PCT'] . " / ";
        echo "Home min odd: " . self::OPTIMAL_VALUES[$tipsterId]['HOME_MIN_ODD'] . " / ";
        echo "Home max odd: " . self::OPTIMAL_VALUES[$tipsterId]['HOME_MAX_ODD'] . " / ";
        echo "Visitor min pct: " . self::OPTIMAL_VALUES[$tipsterId]['VISITOR_MIN_PCT'] . " / ";
        echo "Visitor min odd: " . self::OPTIMAL_VALUES[$tipsterId]['VISITOR_MIN_ODD'] . " / ";
        echo "Visitor max odd: " . self::OPTIMAL_VALUES[$tipsterId]['VISITOR_MAX_ODD']. "\n";
        echo "----------------------------------------------------------------------------------\n\n";

        foreach ($events as $event) {
            // Only play home for now
            if ($event['prediction'] !== "1") {
                continue;
            }

            if (!$this->isValidTip($event, $tipsterId)) {
                continue;
            }

            echo $event['date'] . " " . $event['time'] . ", ". $event['home_team'] . " - " . $event['visitor_team'] . " => ";

            if ($this->isValidHomeTip($event, $tipsterId)) {
                $bet = ($event['bet_1'] == null)
                    ? "-------------------------> MISSING BET"
                    : $event['bet_1'];

                echo " Home pct: $event[home_pct], ";
                echo " Odd: $event[odd_1], ";
                echo " Stake: $event[home_stake], ";
                echo " Bet: $bet \n";
            }
            if ($this->isValidVisitorTip($event, $tipsterId)) {
                $bet = ($event['bet_2'] == null)
                    ? "-------------------------> MISSING BET"
                    : $event['bet_2'];

                echo " Visitor pct: $event[visitor_pct], ";
                echo " Odd: $event[odd_2], ";
                echo " Stake: $event[visitor_stake], ";
                echo " Bet: $bet \n";
            }

            echo "\n";
        }

        echo "----------------------------------------------------------------------------------\n";
        echo "SQLs \n";
        echo "----------------------------------------------------------------------------------\n\n";

        foreach ($events as $event) {
            // Only play home for now
            if ($event['prediction'] !== "1") {
                continue;
            }

            $updateFields = "";

            if (!$this->isValidTip($event, $tipsterId) || ($event['bet_1'] != null || $event['bet_2'] != null)) {
                continue;
            }

            if ($this->isValidHomeTip($event, $tipsterId)) {
                $updateFields .= "bet_1 = $event[odd_1], home_stake = $event[home_stake]";
            }
            if ($this->isValidVisitorTip($event, $tipsterId)) {
                $updateFields .= "bet_2 = $event[odd_2], visitor_stake = $event[visitor_stake]";
            }

            echo "-- $event[home_team] - $event[visitor_team] \n";
            echo "UPDATE event SET $updateFields WHERE id = $event[id];\n\n";
        }

        echo "\n";
        return Command::SUCCESS;
    }

    private function isValidTip(array $event, int $tipsterId): bool
    {
        return $this->isValidHomeTip($event, $tipsterId)
            || $this->isValidVisitorTip($event, $tipsterId);
    }

    private function isValidHomeTip(array $event, int $tipsterId): bool
    {
        return $event['prediction'] === '1'
            && $event['home_pct'] >= self::OPTIMAL_VALUES[$tipsterId]['HOME_MIN_PCT']
            && $event['odd_1'] >= self::OPTIMAL_VALUES[$tipsterId]['HOME_MIN_ODD']
            && $event['odd_1'] <= self::OPTIMAL_VALUES[$tipsterId]['HOME_MAX_ODD'];
    }

    private function isValidVisitorTip(array $event, int $tipsterId): bool
    {
        return $event['prediction'] === '2'
            && $event['visitor_pct'] >= self::OPTIMAL_VALUES[$tipsterId]['VISITOR_MIN_PCT']
            && $event['odd_2'] >= self::OPTIMAL_VALUES[$tipsterId]['VISITOR_MIN_ODD']
            && $event['odd_2'] <= self::OPTIMAL_VALUES[$tipsterId]['VISITOR_MAX_ODD'];
    }
}

