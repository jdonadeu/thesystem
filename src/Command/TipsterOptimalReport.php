<?php

namespace App\Command;

use App\Repository\ReportRepository;
use App\Tipster\ForeBet;
use App\Tipster\Zulu;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tipster:optimal')]
class TipsterOptimalReport extends Command
{
    private const ODD_INCREMENT = 5;

    public function __construct(
        private readonly ReportRepository $reportRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tipsterId', InputArgument::REQUIRED);
    }

    /**
     * @throws Exception
     */
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $start = microtime(true);

        $tipsterId = (int)$input->getArgument('tipsterId');

        if ($tipsterId === 1) {
            $minTipsterPct = Zulu::MIN_PCT;
        } elseif ($tipsterId === 2) {
            $minTipsterPct = ForeBet::MIN_PCT;
        } else {
            throw new Exception("Invalid tipster id[value=$tipsterId]");
        }

        $maxHomeNetGains = 0;
        $optimalHomeMinPct = 0;
        $optimalHomeMinOdd = 0;

        $maxVisitorNetGains = 0;
        $optimalVisitorMinPct = 0;
        $optimalVisitorMinOdd = 0;

        $events = $this->reportRepository->getEventsForSummary($tipsterId, $minTipsterPct, 1, 99);

        for ($minPct = $minTipsterPct; $minPct <= 90; $minPct = $minPct + 2) {
            for ($minOdd = 100; $minOdd <= 600; $minOdd = $minOdd + 5) {
                $filteredEvents = $this->filterEventsByPctAndOdd($events, $minPct, $minOdd / 100, self::ODD_INCREMENT);
                $summary = $this->reportRepository->eventsSummary($filteredEvents);

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

