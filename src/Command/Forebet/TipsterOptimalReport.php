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
class TipsterOptimalReport extends Command
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

        $maxVisitorNetGains = 0;
        $optimalVisitorMinPct = 0;
        $optimalVisitorMinOdd = 0;

        $events = $this->forebetRepository->getMatchesForSummary(ForeBet::MIN_PCT, 1, 99);

        for ($minPct = ForeBet::MIN_PCT; $minPct <= 90; $minPct = $minPct + 2) {
            for ($minOdd = 100; $minOdd <= 600; $minOdd = $minOdd + 5) {
                $filteredEvents = $this->filterEventsByPctAndOdd($events, $minPct, $minOdd / 100, self::ODD_INCREMENT);
                $summary = $this->forebetRepository->matchesSummary($filteredEvents);

                if ($summary['totalHomeNetGains'] <= 0 && $summary['totalVisitorNetGains'] <= 0) {
                    continue;
                }

                if ($summary['totalHomeNetGains'] >= $maxHomeNetGains) {
                    $maxHomeNetGains = $summary['totalHomeNetGains'];
                    $optimalHomeMinPct = $minPct;
                    $optimalHomeMinOdd = $minOdd;
                }

                if ($summary['totalVisitorNetGains'] >= $maxVisitorNetGains) {
                    $maxVisitorNetGains = $summary['totalVisitorNetGains'];
                    $optimalVisitorMinPct = $minPct;
                    $optimalVisitorMinOdd = $minOdd;
                }
            }
        }

        $optimalHomeMinOdd = $optimalHomeMinOdd / 100;
        $optimalVisitorMinOdd = $optimalVisitorMinOdd / 100;

        $optimalHomeMaxOdd = $optimalHomeMinOdd + self::ODD_INCREMENT;
        $optimalVisitorMaxOdd = $optimalVisitorMinOdd + self::ODD_INCREMENT;

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

    private function filterEventsByPctAndOdd(
        array $events,
        int   $minPct,
        float $minOdd,
        float $oddIncrement,
    ): array {
        $maxOdd = $minOdd + $oddIncrement;
        $filteredEvents = [];

        foreach ($events as $event) {
            if ($event['prediction'] === 'X') {
                throw new Exception('Draws are not valid');
            }

            if ($event['prediction'] === '1'
                && ($event['home_pct'] < $minPct || $event['odd_1'] < $minOdd || $event['odd_1'] > $maxOdd)
            ) {
                continue;
            }

            if ($event['prediction'] === '2'
                && ($event['visitor_pct'] < $minPct || $event['odd_2'] < $minOdd || $event['odd_2'] > $maxOdd)
            ) {
                continue;
            }

            $filteredEvents[] = $event;
        }

        return $filteredEvents;
    }
}

