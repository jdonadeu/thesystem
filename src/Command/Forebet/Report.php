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

        $homePredictionsPct = $summary['homePredictions'] === 0
            ? 0
            : floor(($summary['homePredictionsPositive'] * 100) / $summary['homePredictions']);

        $visitorPredictionsPct = $summary['visitorPredictions'] === 0
            ? 0
            :floor(($summary['visitorPredictionsPositive'] * 100) / $summary['visitorPredictions']);

        echo "\n";
        echo "Min Pct: {$minPct} \n";
        echo "Odds: $minOdd - $maxOdd \n";
        echo "\n";
        echo "Home predictions: {$summary['homePredictions']} \n";
        echo "Home wins: {$summary['homePredictionsPositive']} ({$homePredictionsPct}%) \n";
        echo "Home gains: {$summary['homeNetGains']} ({$summary['homeGains']} - {$summary['homeStakes']}) \n";
        echo "\n";
        echo "Visitor predictions: {$summary['visitorPredictions']} \n";
        echo "Visitor wins: {$summary['visitorPredictionsPositive']} ({$visitorPredictionsPct}%) \n";
        echo "Visitor gains: {$summary['visitorNetGains']} ({$summary['visitorGains']} - {$summary['visitorStakes']}) \n";
        echo "\n";

        return Command::SUCCESS;
    }
}

