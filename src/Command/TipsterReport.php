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
        $this->addArgument('oddThreshold', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = $input->getArgument('tipsterId');
        $pctThreshold = $input->getArgument('pctThreshold');
        $oddThreshold = $input->getArgument('oddThreshold');

        $tipsterSummary = $this->reportRepository->predictionsSummaryByTipster($tipsterId, $pctThreshold, $oddThreshold);

        $homePredictionsPct = $tipsterSummary['totalHomePredictions'] === 0
            ? 0
            : floor(($tipsterSummary['totalHomePredictionsPositive'] * 100) / $tipsterSummary['totalHomePredictions']);

        $homeOrDrawPredictionsPct = $tipsterSummary['totalHomeOrDrawPredictions'] === 0
            ? 0
            : floor(($tipsterSummary['totalHomeOrDrawPredictionsPositive'] * 100) / $tipsterSummary['totalHomeOrDrawPredictions']);

        $visitorPredictionsPct = $tipsterSummary['totalVisitorPredictions'] === 0
            ? 0
            :floor(($tipsterSummary['totalVisitorPredictionsPositive'] * 100) / $tipsterSummary['totalVisitorPredictions']);

        $drawOrVisitorPredictionsPct = $tipsterSummary['totalDrawOrVisitorPredictions'] === 0
            ? 0
            : floor(($tipsterSummary['totalDrawOrVisitorPredictionsPositive'] * 100) / $tipsterSummary['totalDrawOrVisitorPredictions']);

        $homeNetGains = $tipsterSummary['totalHomeGains'] - $tipsterSummary['totalHomePredictions'];
        $homeOrDrawNetGains = $tipsterSummary['totalHomeOrDrawGains'] - $tipsterSummary['totalHomeOrDrawPredictions'];
        $visitorNetGains = $tipsterSummary['totalVisitorGains'] - $tipsterSummary['totalVisitorPredictions'];
        $drawOrVisitorNetGains = $tipsterSummary['totalDrawOrVisitorGains'] - $tipsterSummary['totalDrawOrVisitorPredictions'];

        echo "\n";
        echo "Tipster: $tipsterId \n";
        echo "Pct threshold: {$pctThreshold} \n";
        echo "Odd threshold: {$oddThreshold} \n";
        echo "Events: {$tipsterSummary['totalEvents']} \n";
        echo "\n";
        echo "Home predictions: {$tipsterSummary['totalHomePredictions']} \n";
        echo "Home wins: {$tipsterSummary['totalHomePredictionsPositive']} ({$homePredictionsPct}%) \n";
        echo "Home gains: {$homeNetGains} ({$tipsterSummary['totalHomeGains']} - {$tipsterSummary['totalHomePredictions']}) \n";
        echo "\n";
        echo "Home or draw predictions: {$tipsterSummary['totalHomeOrDrawPredictions']} \n";
        echo "Home or draw wins: {$tipsterSummary['totalHomeOrDrawPredictionsPositive']} ({$homeOrDrawPredictionsPct}%) \n";
        echo "Home or draw gains: {$homeOrDrawNetGains} ({$tipsterSummary['totalHomeOrDrawGains']} - {$tipsterSummary['totalHomeOrDrawPredictions']}) \n";
        echo "\n";
        echo "Visitor predictions: {$tipsterSummary['totalVisitorPredictions']} \n";
        echo "Visitor wins: {$tipsterSummary['totalVisitorPredictionsPositive']} ({$visitorPredictionsPct}%) \n";
        echo "Visitor gains: {$visitorNetGains} ({$tipsterSummary['totalVisitorGains']} - {$tipsterSummary['totalVisitorPredictions']}) \n";
        echo "\n";
        echo "Draw or visitor predictions: {$tipsterSummary['totalDrawOrVisitorPredictions']} \n";
        echo "Draw or visitor wins: {$tipsterSummary['totalDrawOrVisitorPredictionsPositive']} ({$drawOrVisitorPredictionsPct}%) \n";
        echo "Draw or visitor gains: {$drawOrVisitorNetGains} ({$tipsterSummary['totalDrawOrVisitorGains']} - {$tipsterSummary['totalDrawOrVisitorPredictions']}) \n";
        echo "\n";

        return Command::SUCCESS;
    }
}

