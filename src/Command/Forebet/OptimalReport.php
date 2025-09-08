<?php

namespace App\Command\Forebet;

use App\Repository\ForebetRepository;
use App\Tipster\ForeBet;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forebet:optimal')]
class OptimalReport extends Command
{
    private const ODD_INCREMENT = 5;

    public function __construct(private readonly ForebetRepository $forebetRepository)
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $start = microtime(true);

        $maxHomeNetGains = 0;
        $optimalHomeMinPct = 0;
        $optimalHomeMinOdd = 0;
        $optimalHomeMaxOdd = 0;

        $maxVisitorNetGains = 0;
        $optimalVisitorMinPct = 0;
        $optimalVisitorMinOdd = 0;
        $optimalVisitorMaxOdd = 0;

        $matches = $this->forebetRepository->getMatchesForSummary(ForeBet::MIN_PCT, 1, 99);

        for ($minPct = ForeBet::MIN_PCT; $minPct <= 90; $minPct = $minPct + 1) {
            for ($minOdd = 100; $minOdd <= 600; $minOdd = $minOdd + 5) {
                //$maxOdd = $minOdd + self::ODD_INCREMENT;
                $maxOdd = 10000;
                $filteredMatches = $this->filterMatchesByPctAndOdd($matches, $minPct, $minOdd / 100, $maxOdd / 100);
                $summary = $this->forebetRepository->matchesSummary($filteredMatches);

                if ($summary['totalHomeNetGains'] <= 0 && $summary['totalVisitorNetGains'] <= 0) {
                    continue;
                }

                if ($summary['totalHomeNetGains'] >= $maxHomeNetGains) {
                    $maxHomeNetGains = $summary['totalHomeNetGains'];
                    $optimalHomeMinPct = $minPct;
                    $optimalHomeMinOdd = $minOdd;
                    $optimalHomeMaxOdd = $maxOdd;
                }

                if ($summary['totalVisitorNetGains'] >= $maxVisitorNetGains) {
                    $maxVisitorNetGains = $summary['totalVisitorNetGains'];
                    $optimalVisitorMinPct = $minPct;
                    $optimalVisitorMinOdd = $minOdd;
                    $optimalVisitorMaxOdd = $maxOdd;
                }
            }
        }

        $optimalHomeMinOdd = $optimalHomeMinOdd / 100;
        $optimalHomeMaxOdd = $optimalHomeMaxOdd / 100;

        $optimalVisitorMinOdd = $optimalVisitorMinOdd / 100;
        $optimalVisitorMaxOdd = $optimalVisitorMaxOdd / 100;

        echo "\n";
        echo "HOME\n";
        echo "Min pct: $optimalHomeMinPct \n";
        echo "Min odd: $optimalHomeMinOdd \n";
        echo "Max odd: $optimalHomeMaxOdd \n";
        echo "Net gains: $maxHomeNetGains \n\n";

        echo "VISITOR\n";
        echo "Min pct: $optimalVisitorMinPct \n";
        echo "Min odd: $optimalVisitorMinOdd \n";
        echo "Max odd: $optimalVisitorMaxOdd \n";
        echo "Net gains: $maxVisitorNetGains \n\n";

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

