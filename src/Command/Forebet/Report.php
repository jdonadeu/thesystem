<?php

namespace App\Command\Forebet;

use App\Repository\ForebetRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forebet:report')]
class Report extends Command
{
    public function __construct(private readonly ForebetRepository $forebetRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('minPct', InputArgument::REQUIRED);
        $this->addArgument('minOdd', InputArgument::REQUIRED);
        $this->addArgument('maxOdd', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $minPct = $input->getArgument('minPct');
        $minOdd = $input->getArgument('minOdd');
        $maxOdd = $input->getArgument('maxOdd');

        $matches = $this->forebetRepository->getMatchesForSummary($minPct, $minOdd, $maxOdd, 6);
        $summary = $this->forebetRepository->matchesSummary($matches);

        $homePredictionsPct = $summary['totalHomePredictions'] === 0
            ? 0
            : floor(($summary['totalHomePredictionsPositive'] * 100) / $summary['totalHomePredictions']);

        $visitorPredictionsPct = $summary['totalVisitorPredictions'] === 0
            ? 0
            :floor(($summary['totalVisitorPredictionsPositive'] * 100) / $summary['totalVisitorPredictions']);

        echo "\n";
        echo "Min Pct: {$minPct} \n";
        echo "Odds: $minOdd - $maxOdd \n";
        echo "\n";
        echo "Home predictions: {$summary['totalHomePredictions']} \n";
        echo "Home wins: {$summary['totalHomePredictionsPositive']} ({$homePredictionsPct}%) \n";
        echo "Home gains: {$summary['totalHomeNetGains']} ({$summary['totalHomeGains']} - {$summary['totalHomeStakes']}) \n";
        echo "\n";
        echo "Visitor predictions: {$summary['totalVisitorPredictions']} \n";
        echo "Visitor wins: {$summary['totalVisitorPredictionsPositive']} ({$visitorPredictionsPct}%) \n";
        echo "Visitor gains: {$summary['totalVisitorNetGains']} ({$summary['totalVisitorGains']} - {$summary['totalVisitorStakes']}) \n";
        echo "\n";

        return Command::SUCCESS;
    }
}

