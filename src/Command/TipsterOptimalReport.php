<?php

namespace App\Command;

use App\Repository\ReportRepository;
use App\Tipster\ForeBet;
use App\Tipster\Zulu;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tipster:optimal')]
class TipsterOptimalReport extends Command
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tipsterId', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = (int)$input->getArgument('tipsterId');

        if ($tipsterId === 1) {
            $minPctThreshold = Zulu::MIN_PCT_THRESHOLD;
        } elseif ($tipsterId === 2) {
            $minPctThreshold = ForeBet::MIN_PCT_THRESHOLD;
        } else {
            throw new Exception("Invalid tipster id[value=$tipsterId]");
        }

        $maxHomeNetGains = 0;
        $optimalHomePctThreshold = 0;
        $optimalHomeOddThreshold = 0;

        $maxHomeOrDrawNetGains = 0;
        $optimalHomeOrDrawPctThreshold = 0;
        $optimalHomeOrDrawOddThreshold = 0;

        $maxVisitorNetGains = 0;
        $optimalVisitorPctThreshold = 0;
        $optimalVisitorOddThreshold = 0;

        $maxDrawOrVisitorNetGains = 0;
        $optimalDrawOrVisitorPctThreshold = 0;
        $optimalDrawOrVisitorOddThreshold = 0;

        for ($pctThreshold = $minPctThreshold; $pctThreshold <= 100; $pctThreshold = $pctThreshold + 5) {
            for ($oddThreshold = 100; $oddThreshold <= 300; $oddThreshold = $oddThreshold + 1) {
                $tipsterSummary = $this->reportRepository->predictionsSummaryByTipster(
                    $tipsterId,
                    $pctThreshold,
                    $oddThreshold / 100
                );

                $homeNetGains = $tipsterSummary['totalHomeGains'] - $tipsterSummary['totalHomePredictions'];
                $homeOrDrawNetGains = $tipsterSummary['totalHomeOrDrawGains'] - $tipsterSummary['totalHomeOrDrawPredictions'];
                $visitorNetGains = $tipsterSummary['totalVisitorGains'] - $tipsterSummary['totalVisitorPredictions'];
                $drawOrVisitorNetGains = $tipsterSummary['totalDrawOrVisitorGains'] - $tipsterSummary['totalDrawOrVisitorPredictions'];

                if ($homeNetGains <= 0
                    && $homeOrDrawNetGains <= 0
                    && $visitorNetGains <= 0
                    && $drawOrVisitorNetGains <= 0
                ) {
                    continue;
                }

                if ($homeNetGains >= $maxHomeNetGains) {
                    $maxHomeNetGains = $homeNetGains;
                    $optimalHomePctThreshold = $pctThreshold;
                    $optimalHomeOddThreshold = $oddThreshold;
                }

                if ($homeOrDrawNetGains >= $maxHomeOrDrawNetGains) {
                    $maxHomeOrDrawNetGains = $homeOrDrawNetGains;
                    $optimalHomeOrDrawPctThreshold = $pctThreshold;
                    $optimalHomeOrDrawOddThreshold = $oddThreshold;
                }

                if ($visitorNetGains >= $maxVisitorNetGains) {
                    $maxVisitorNetGains = $visitorNetGains;
                    $optimalVisitorPctThreshold = $pctThreshold;
                    $optimalVisitorOddThreshold = $oddThreshold;
                }

                if ($drawOrVisitorNetGains >= $maxDrawOrVisitorNetGains) {
                    $maxDrawOrVisitorNetGains = $drawOrVisitorNetGains;
                    $optimalDrawOrVisitorPctThreshold = $pctThreshold;
                    $optimalDrawOrVisitorOddThreshold = $oddThreshold;
                }
            }
        }

        $optimalHomeOddThreshold = $optimalHomeOddThreshold / 100;
        $optimalHomeOrDrawOddThreshold = $optimalHomeOrDrawOddThreshold / 100;
        $optimalVisitorOddThreshold = $optimalVisitorOddThreshold / 100;
        $optimalDrawOrVisitorOddThreshold = $optimalDrawOrVisitorOddThreshold / 100;

        echo "\n";
        echo "Home: optimal pct, optimal odd, net gains\n";
        echo "$optimalHomePctThreshold, $optimalHomeOddThreshold, $maxHomeNetGains \n\n";

        echo "Home or draw: optimal pct, optimal odd, net gains\n";
        echo "$optimalHomeOrDrawPctThreshold, $optimalHomeOrDrawOddThreshold, $maxHomeOrDrawNetGains \n\n";

        echo "Visitor: optimal pct, optimal odd, net gains\n";
        echo "$optimalVisitorPctThreshold, $optimalVisitorOddThreshold, $maxVisitorNetGains \n\n";

        echo "Draw or visitor: optimal pct, optimal odd, net gains\n";
        echo "$optimalDrawOrVisitorPctThreshold, $optimalDrawOrVisitorOddThreshold, $maxDrawOrVisitorNetGains \n";
        echo "\n";

        return Command::SUCCESS;
    }
}

