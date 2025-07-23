<?php

namespace App\Command;

use App\Repository\ReportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tipster:report')]
class TipsterReport extends Command
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tipsterId', InputArgument::REQUIRED);
        $this->addArgument('minPct', InputArgument::REQUIRED);
        $this->addArgument('minOdd', InputArgument::REQUIRED);
        $this->addArgument('maxOdd', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = $input->getArgument('tipsterId');
        $minPct = $input->getArgument('minPct');
        $minOdd = $input->getArgument('minOdd');
        $maxOdd = $input->getArgument('maxOdd');

        $events = $this->reportRepository->getEventsForSummary($tipsterId, $minPct, $minOdd, $maxOdd);
        $summary = $this->reportRepository->eventsSummary($events);

        $homePredictionsPct = $summary['totalHomePredictions'] === 0
            ? 0
            : floor(($summary['totalHomePredictionsPositive'] * 100) / $summary['totalHomePredictions']);

        $visitorPredictionsPct = $summary['totalVisitorPredictions'] === 0
            ? 0
            :floor(($summary['totalVisitorPredictionsPositive'] * 100) / $summary['totalVisitorPredictions']);

        $homeNetGains = $summary['totalHomeGains'] - $summary['totalHomePredictions'];
        $visitorNetGains = $summary['totalVisitorGains'] - $summary['totalVisitorPredictions'];

        echo "\n";
        echo "Tipster: $tipsterId \n";
        echo "Min Pct: {$minPct} \n";
        echo "Odds: $minOdd - $maxOdd \n";
        echo "Events: {$summary['totalEvents']} \n";
        echo "\n";
        echo "Home predictions: {$summary['totalHomePredictions']} \n";
        echo "Home wins: {$summary['totalHomePredictionsPositive']} ({$homePredictionsPct}%) \n";
        echo "Home gains: {$homeNetGains} ({$summary['totalHomeGains']} - {$summary['totalHomePredictions']}) \n";
        echo "\n";
        echo "Visitor predictions: {$summary['totalVisitorPredictions']} \n";
        echo "Visitor wins: {$summary['totalVisitorPredictionsPositive']} ({$visitorPredictionsPct}%) \n";
        echo "Visitor gains: {$visitorNetGains} ({$summary['totalVisitorGains']} - {$summary['totalVisitorPredictions']}) \n";
        echo "\n";

        return Command::SUCCESS;
    }
}

