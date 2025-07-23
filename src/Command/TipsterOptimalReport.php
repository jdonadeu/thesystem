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
        $optimalHomePctThreshold = 0;
        $optimalHomeOddThreshold = 0;

        $maxVisitorNetGains = 0;
        $optimalVisitorPctThreshold = 0;
        $optimalVisitorOddThreshold = 0;

        $events = $this->reportRepository->getEventsForSummary($tipsterId, $minTipsterPct, 1, 99);

        for ($minPct = $minTipsterPct; $minPct <= 90; $minPct = $minPct + 2) {
            for ($minOdd = 100; $minOdd <= 600; $minOdd = $minOdd + 5) {
                $filteredEvents = $this->filterEventsByPctAndOdd($events, $minPct, $minOdd / 100);
                $summary = $this->reportRepository->eventsSummary($filteredEvents);

                $homeNetGains = $summary['totalHomeGains'] - $summary['totalHomePredictions'];
                $visitorNetGains = $summary['totalVisitorGains'] - $summary['totalVisitorPredictions'];

                if ($homeNetGains <= 0 && $visitorNetGains <= 0) {
                    continue;
                }

                if ($homeNetGains >= $maxHomeNetGains) {
                    $maxHomeNetGains = $homeNetGains;
                    $optimalHomePctThreshold = $minPct;
                    $optimalHomeOddThreshold = $minOdd;
                }

                if ($visitorNetGains >= $maxVisitorNetGains) {
                    $maxVisitorNetGains = $visitorNetGains;
                    $optimalVisitorPctThreshold = $minPct;
                    $optimalVisitorOddThreshold = $minOdd;
                }
            }
        }

        $optimalHomeOddThreshold = $optimalHomeOddThreshold / 100;
        $optimalVisitorOddThreshold = $optimalVisitorOddThreshold / 100;

        echo "\n";
        echo "Home: optimal pct, optimal odd, net gains\n";
        echo "$optimalHomePctThreshold, $optimalHomeOddThreshold, $maxHomeNetGains \n\n";

        echo "Visitor: optimal pct, optimal odd, net gains\n";
        echo "$optimalVisitorPctThreshold, $optimalVisitorOddThreshold, $maxVisitorNetGains \n\n";

        $end = microtime(true);
        $executionTime = $end - $start;
        echo "\nExecution time: " . floor($executionTime) . " seconds\n\n";

        return Command::SUCCESS;
    }

    private function filterEventsByPctAndOdd(array $events, int $pctThreshold, float $oddThreshold): array
    {
        $filteredEvents = [];

        foreach ($events as $event) {
            if ($event['prediction'] === 'X') {
                throw new Exception('Draws are not valid');
            }

            if (($event['prediction'] === '1' && ($event['home_pct'] < $pctThreshold || $event['odd_1'] < $oddThreshold))
                || ($event['prediction'] === '2' && ($event['visitor_pct'] < $pctThreshold || $event['odd_2'] < $oddThreshold))
            ) {
                continue;
            }

            $filteredEvents[] = $event;
        }

        return $filteredEvents;
    }
}

