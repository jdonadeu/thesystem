<?php

namespace App\Command;

use App\Repository\ReportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tipster:tips')]
class TipsterTips extends Command
{
    private const OPTIMAL_VALUES = [
        1 => [
            'HOME_PCT' => 55,
            'ODD_1' => 2.50,
            'VISITOR_PCT' => 50,
            'ODD_2' => 2.20,
        ],
        2 => [
            'HOME_PCT' => 43,
            'ODD_1' => 2.88,
            'VISITOR_PCT' => 37,
            'ODD_2' => 3.80,
        ]
    ];

    public function __construct(private readonly ReportRepository $reportRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tipsterId', InputArgument::REQUIRED);
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tipsterId = $input->getArgument('tipsterId');

        $tips = $this->reportRepository->tips(
            $tipsterId,
            self::OPTIMAL_VALUES[$tipsterId]['HOME_PCT'],
            self::OPTIMAL_VALUES[$tipsterId]['ODD_1'],
            self::OPTIMAL_VALUES[$tipsterId]['VISITOR_PCT'],
            self::OPTIMAL_VALUES[$tipsterId]['ODD_2'],
        );

        echo "\n";
        echo "TipsterId, dateTime, home, visitor, odd_1, odd_2, home_pct, visitor_pct \n";
        echo "------------------------------------------------------------------------\n";

        foreach ($tips as $tip) {
            $isHome = $tip['home_pct'] >= $tip['visitor_pct'];

            $odd1 = $isHome ? "({$tip['odd_1']})" : $tip['odd_1'];
            $homePct = $isHome ? "({$tip['home_pct']})" : $tip['home_pct'];

            $odd2 = !$isHome ? "({$tip['odd_2']})" : $tip['odd_2'];
            $visitorPct = !$isHome ? "({$tip['visitor_pct']})" : $tip['visitor_pct'];

            echo $tipsterId . ", ";
            echo $tip['date'] . " " . $tip['time'] . ", ";
            echo $tip['home_team'] . ", ";
            echo $tip['visitor_team'] . ", ";
            echo $odd1 . ", ";
            echo $odd2 . ", ";
            echo $homePct . ", ";
            echo $visitorPct . ", ";
            echo $isHome ? "=> (home)" : "=> (visitor)";
            echo "\n";
        }

        echo "\n";

        return Command::SUCCESS;
    }
}

