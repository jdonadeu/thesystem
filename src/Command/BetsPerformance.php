<?php

namespace App\Command;

use App\Repository\ReportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'bets:performance')]
class BetsPerformance extends Command
{
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

        $bets = $this->reportRepository->placedBets($tipsterId);

        $bet1Count = 0;
        $bet1Gains = 0;
        $bet1Wins = 0;

        $bet1xCount = 0;
        $bet1xGains = 0;
        $bet1xWins = 0;

        $betx2Count = 0;
        $betx2Gains = 0;
        $betx2Wins = 0;

        $bet2Count = 0;
        $bet2Gains = 0;
        $bet2Wins = 0;

        foreach ($bets as $bet) {
            if ($bet['bet_1'] !== null) {
                $bet1Count++;

                if ($bet['homeWin'] === 1) {
                    $bet1Wins++;
                    $bet1Gains += $bet['bet_1'];
                }
            }

            if ($bet['bet_1x'] !== null) {
                $bet1xCount++;

                if ($bet['homeWin'] === 1 || $bet['drawWin'] === 1) {
                    $bet1xWins++;
                    $bet1xGains += $bet['bet_1x'];
                }
            }

            if ($bet['bet_x2'] !== null) {
                $betx2Count++;

                if ($bet['drawWin'] === 1 || $bet['visitorWin'] === 1) {
                    $betx2Wins++;
                    $betx2Gains += $bet['bet_x2'];
                }
            }

            if ($bet['bet_2'] !== null) {
                $bet2Count++;

                if ($bet['visitorWin'] === 1) {
                    $bet2Wins++;
                    $bet2Gains += $bet['bet_2'];
                }
            }
        }

        $bet1Pct = $bet1Count === 0 ? 0 : floor(($bet1Wins / $bet1Count) * 100);
        $bet1xPct = $bet1xCount === 0 ? 0 : floor(($bet1xWins / $bet1xCount) * 100);
        $betx2Pct = $betx2Count === 0 ? 0 : floor(($betx2Wins / $betx2Count) * 100);
        $bet2Pct = $bet2Count === 0 ? 0 : floor(($bet2Wins / $bet2Count) * 100);

        $bet1NetGains = $bet1Gains - $bet1Count;
        $bet1xNetGains = $bet1xGains - $bet1xCount;
        $betx2NetGains = $betx2Gains - $betx2Count;
        $bet2NetGains = $bet2Gains - $bet2Count;

        echo "\n";
        echo "Home => count: $bet1Count, wins: $bet1Wins, net gains: $bet1NetGains, pct: $bet1Pct% \n\n";
        echo "Home or draw => count: $bet1xCount, wins: $bet1xWins, net gains: $bet1xNetGains, pct: $bet1xPct% \n\n";
        echo "Draw or visitor => count: $betx2Count, wins: $betx2Wins, net gains: $betx2NetGains, pct: $betx2Pct% \n\n";
        echo "Visitor => count: $bet2Count, wins: $bet2Wins, net gains: $bet2NetGains, pct: $bet2Pct% \n\n";
        echo "\n";

        return Command::SUCCESS;
    }
}

