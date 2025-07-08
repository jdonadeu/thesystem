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
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = $input->getArgument('tipsterId');
        $pctThreshold = $input->getArgument('pctThreshold');

        $tipsterSummary = $this->reportRepository->predictionsSummaryByTipster($tipsterId, $pctThreshold);

        $homePredictionsPct = $tipsterSummary['totalHomePredictions'] === 0
            ? 0
            : floor(($tipsterSummary['totalHomePredictionsPositive'] * 100) / $tipsterSummary['totalHomePredictions']);

        $visitorPredictionsPct = $tipsterSummary['totalVisitorPredictions'] === 0
            ? 0
            :floor(($tipsterSummary['totalVisitorPredictionsPositive'] * 100) / $tipsterSummary['totalVisitorPredictions']);

        echo "Tipster: {$tipsterSummary['tipsterName']} \n";
        echo "Pct threshold: {$pctThreshold} \n";
        echo "Events: {$tipsterSummary['totalEvents']} \n";
        echo "Home predictions: {$tipsterSummary['totalHomePredictions']} \n";
        echo "Home wins: {$tipsterSummary['totalHomePredictionsPositive']} ({$homePredictionsPct}%) \n";
        echo "Visitor predictions: {$tipsterSummary['totalVisitorPredictions']} \n";
        echo "Visitor wins: {$tipsterSummary['totalVisitorPredictionsPositive']} ({$visitorPredictionsPct}%) \n";

        return Command::SUCCESS;
    }
}

