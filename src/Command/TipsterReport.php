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
        $this->addArgument('pctThreshold', InputArgument::REQUIRED);
        $this->addArgument('minOdd', InputArgument::REQUIRED);
        $this->addArgument('maxOdd', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = $input->getArgument('tipsterId');
        $pctThreshold = $input->getArgument('pctThreshold');
        $minOdd = $input->getArgument('minOdd');
        $maxOdd = $input->getArgument('maxOdd');

        $events = $this->reportRepository->getEventsForSummary($tipsterId, $pctThreshold, $minOdd, $maxOdd);
        $summary = $this->reportRepository->eventsSummary($events);

        $homePredictionsPct = $summary['totalHomePredictions'] === 0
            ? 0
            : floor(($summary['totalHomePredictionsPositive'] * 100) / $summary['totalHomePredictions']);

        $homeOrDrawPredictionsPct = $summary['totalHomeOrDrawPredictions'] === 0
            ? 0
            : floor(($summary['totalHomeOrDrawPredictionsPositive'] * 100) / $summary['totalHomeOrDrawPredictions']);

        $visitorPredictionsPct = $summary['totalVisitorPredictions'] === 0
            ? 0
            :floor(($summary['totalVisitorPredictionsPositive'] * 100) / $summary['totalVisitorPredictions']);

        $drawOrVisitorPredictionsPct = $summary['totalDrawOrVisitorPredictions'] === 0
            ? 0
            : floor(($summary['totalDrawOrVisitorPredictionsPositive'] * 100) / $summary['totalDrawOrVisitorPredictions']);

        $homeNetGains = $summary['totalHomeGains'] - $summary['totalHomePredictions'];
        $homeOrDrawNetGains = $summary['totalHomeOrDrawGains'] - $summary['totalHomeOrDrawPredictions'];
        $visitorNetGains = $summary['totalVisitorGains'] - $summary['totalVisitorPredictions'];
        $drawOrVisitorNetGains = $summary['totalDrawOrVisitorGains'] - $summary['totalDrawOrVisitorPredictions'];

        echo "\n";
        echo "Tipster: $tipsterId \n";
        echo "Pct threshold: {$pctThreshold} \n";
        echo "Odds: $minOdd - $maxOdd \n";
        echo "Events: {$summary['totalEvents']} \n";
        echo "\n";
        echo "Home predictions: {$summary['totalHomePredictions']} \n";
        echo "Home wins: {$summary['totalHomePredictionsPositive']} ({$homePredictionsPct}%) \n";
        echo "Home gains: {$homeNetGains} ({$summary['totalHomeGains']} - {$summary['totalHomePredictions']}) \n";
        echo "\n";
        echo "Home or draw predictions: {$summary['totalHomeOrDrawPredictions']} \n";
        echo "Home or draw wins: {$summary['totalHomeOrDrawPredictionsPositive']} ({$homeOrDrawPredictionsPct}%) \n";
        echo "Home or draw gains: {$homeOrDrawNetGains} ({$summary['totalHomeOrDrawGains']} - {$summary['totalHomeOrDrawPredictions']}) \n";
        echo "\n";
        echo "Visitor predictions: {$summary['totalVisitorPredictions']} \n";
        echo "Visitor wins: {$summary['totalVisitorPredictionsPositive']} ({$visitorPredictionsPct}%) \n";
        echo "Visitor gains: {$visitorNetGains} ({$summary['totalVisitorGains']} - {$summary['totalVisitorPredictions']}) \n";
        echo "\n";
        echo "Draw or visitor predictions: {$summary['totalDrawOrVisitorPredictions']} \n";
        echo "Draw or visitor wins: {$summary['totalDrawOrVisitorPredictionsPositive']} ({$drawOrVisitorPredictionsPct}%) \n";
        echo "Draw or visitor gains: {$drawOrVisitorNetGains} ({$summary['totalDrawOrVisitorGains']} - {$summary['totalDrawOrVisitorPredictions']}) \n";
        echo "\n";

        return Command::SUCCESS;
    }
}

