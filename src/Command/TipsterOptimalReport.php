<?php

namespace App\Command;

use App\Repository\ReportRepository;
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
        $tipsterId = $input->getArgument('tipsterId');
        $optimalHomePctThreshold = 0;
        $optimalHomeOddThreshold = 0;
        $optimalVisitorPctThreshold = 0;
        $optimalVisitorOddThreshold = 0;
        $maxHomeNetGains = 0;
        $maxVisitorNetGains = 0;

        for ($pctThreshold = 50; $pctThreshold <= 100; $pctThreshold = $pctThreshold + 5) {
            for ($oddThreshold = 100; $oddThreshold <= 300; $oddThreshold = $oddThreshold + 1) {
                $tipsterSummary = $this->reportRepository->predictionsSummaryByTipster(
                    $tipsterId,
                    $pctThreshold,
                    $oddThreshold / 100
                );

                $homeNetGains = $tipsterSummary['totalHomeGains'] - $tipsterSummary['totalHomePredictions'];
                $visitorNetGains = $tipsterSummary['totalVisitorGains'] - $tipsterSummary['totalVisitorPredictions'];

                if ($homeNetGains <= 0 && $visitorNetGains <= 0) {
                    continue;
                }

                if ($homeNetGains >= $maxHomeNetGains) {
                    $maxHomeNetGains = $homeNetGains;
                    $optimalHomePctThreshold = $pctThreshold;
                    $optimalHomeOddThreshold = $oddThreshold;
                }

                if ($visitorNetGains >= $maxVisitorNetGains) {
                    $maxVisitorNetGains = $visitorNetGains;
                    $optimalVisitorPctThreshold = $pctThreshold;
                    $optimalVisitorOddThreshold = $oddThreshold;
                }
            }
        }

        $optimalHomeOddThreshold = $optimalHomeOddThreshold / 100;
        $optimalVisitorOddThreshold = $optimalVisitorOddThreshold / 100;

        echo "\n";
        echo "Home: optimal pct, optimal odd, net gains\n";
        echo "$optimalHomePctThreshold, $optimalHomeOddThreshold, $maxHomeNetGains \n\n";
        echo "Visitor: optimal pct, optimal odd, net gains\n";
        echo "$optimalVisitorPctThreshold, $optimalVisitorOddThreshold, $maxVisitorNetGains \n";
        echo "\n";

        return Command::SUCCESS;
    }
}

