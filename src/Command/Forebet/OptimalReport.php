<?php

namespace App\Command\Forebet;

use App\Repository\ForebetRepository;
use App\Tipster\ForeBet;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forebet:optimal')]
class OptimalReport extends Command
{
    public function __construct(private readonly ForebetRepository $forebetRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::OPTIONAL);
    }

    /**
     * @throws Exception
     */
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');

        if ($type !== 'ratio' && $type !== 'net') {
            throw new Exception("invalid type");
        }

        $start = microtime(true);

        $optimalHomeMinPct = 0;
        $optimalHomeMinOdd = 0;
        $optimalHomeMaxOdd = 0;
        $optimalHomeSummary = [];

        $optimalVisitorMinPct = 0;
        $optimalVisitorMinOdd = 0;
        $optimalVisitorMaxOdd = 0;
        $optimalVisitorSummary = [];

        $matches = $this->forebetRepository->getMatchesForSummary(ForeBet::MIN_PCT, 1, 100, 99);
        echo "Analyzing " . count($matches) . " matches\n\n";

        for ($minPct = ForeBet::MIN_PCT; $minPct <= 95; $minPct = $minPct + 1) {
            for ($minOdd = 100; $minOdd <= 1000; $minOdd = $minOdd + 5) {
                $maxOdd = 10000;
                $filteredMatches = $this->filterMatchesByPctAndOdd($matches, $minPct, $minOdd / 100, $maxOdd / 100);
                $summary = $this->forebetRepository->matchesSummary($filteredMatches);

                if ($type === 'ratio') {
                    if ($summary['homePredictions'] >= 100) {
                        $maxHomeNetGainsStakeRatio = ($optimalHomeSummary['homeNetGains'] ?? 0) / ($optimalHomeSummary['homeStakes'] ?? 1);
                        $homeNetGainsStakeRatio = $summary['homeNetGains'] / $summary['homeStakes'];

                        if ($homeNetGainsStakeRatio >= $maxHomeNetGainsStakeRatio) {
                            $optimalHomeSummary = $summary;
                            $optimalHomeMinPct = $minPct;
                            $optimalHomeMinOdd = $minOdd;
                            $optimalHomeMaxOdd = $maxOdd;
                        }
                    }

                    if ($summary['visitorPredictions'] >= 100) {
                        $maxVisitorNetGainsStakeRatio = ($optimalVisitorSummary['visitorNetGains'] ?? 0) / ($optimalVisitorSummary['visitorStakes'] ?? 1);
                        $visitorNetGainsStakeRatio = $summary['visitorNetGains'] / $summary['visitorStakes'];

                        if ($visitorNetGainsStakeRatio >= $maxVisitorNetGainsStakeRatio) {
                            $optimalVisitorSummary = $summary;
                            $optimalVisitorMinPct = $minPct;
                            $optimalVisitorMinOdd = $minOdd;
                            $optimalVisitorMaxOdd = $maxOdd;
                        }
                    }
                }

                if ($type === 'net') {
                    if ($summary['homeNetGains'] > ($optimalHomeSummary['homeNetGains'] ?? 0)) {
                        $optimalHomeSummary = $summary;
                        $optimalHomeMinPct = $minPct;
                        $optimalHomeMinOdd = $minOdd;
                        $optimalHomeMaxOdd = $maxOdd;
                    }

                    if ($summary['visitorNetGains'] > ($optimalVisitorSummary['visitorNetGains'] ?? 0)) {
                        $optimalVisitorSummary = $summary;
                        $optimalVisitorMinPct = $minPct;
                        $optimalVisitorMinOdd = $minOdd;
                        $optimalVisitorMaxOdd = $maxOdd;
                    }
                }
            }
        }

        $homeNetGainsStakeRatio = $optimalHomeSummary['homeNetGains'] / $optimalHomeSummary['homeStakes'];
        $visitorNetGainsStakeRatio = $optimalVisitorSummary['visitorNetGains'] / $optimalVisitorSummary['visitorStakes'];

        echo "\n";
        echo "HOME\n";
        echo "Total matches: $optimalHomeSummary[homePredictions] \n";
        echo "Min pct: $optimalHomeMinPct \n";
        echo "Min odd: " . $optimalHomeMinOdd / 100 . "\n";
        echo "Max odd: " . $optimalHomeMaxOdd / 100 . "\n";
        echo "Stakes: $optimalHomeSummary[homeStakes] \n";
        echo "Gains: $optimalHomeSummary[homeGains] \n";
        echo "Net gains: $optimalHomeSummary[homeNetGains] \n";
        echo "Net gains/stake ratio: $homeNetGainsStakeRatio \n\n";

        echo "VISITOR\n";
        echo "Total matches: $optimalVisitorSummary[visitorPredictions] \n";
        echo "Min pct: $optimalVisitorMinPct \n";
        echo "Min odd: " . $optimalVisitorMinOdd / 100 . "\n";
        echo "Max odd: " . $optimalVisitorMaxOdd / 100 . "\n";
        echo "Stakes: $optimalVisitorSummary[visitorStakes] \n";
        echo "Gains: $optimalVisitorSummary[visitorGains] \n";
        echo "Net gains: $optimalVisitorSummary[visitorNetGains] \n";
        echo "Net gains/stake ratio: $visitorNetGainsStakeRatio \n\n";

        $end = microtime(true);
        $executionTime = $end - $start;
        echo "\nExecution time: " . floor($executionTime) . " seconds\n\n";

        return Command::SUCCESS;
    }

    private function filterMatchesByPctAndOdd(array $matches, int $minPct, float $minOdd, float $maxOdd): array
    {
        $filteredMatches = [];

        foreach ($matches as $match) {
            if ($match['prediction'] === 'X') {
                throw new Exception('Draws are not valid');
            }

            if ($match['prediction'] === '1'
                && ($match['home_pct'] < $minPct || $match['odd_1'] < $minOdd || $match['odd_1'] > $maxOdd)
            ) {
                continue;
            }

            if ($match['prediction'] === '2'
                && ($match['visitor_pct'] < $minPct || $match['odd_2'] < $minOdd || $match['odd_2'] > $maxOdd)
            ) {
                continue;
            }

            $filteredMatches[] = $match;
        }

        return $filteredMatches;
    }
}

