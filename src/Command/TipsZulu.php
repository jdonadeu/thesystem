<?php

namespace App\Command;

use App\Repository\ReportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tips:zulu')]
class TipsZulu extends Command
{
    private const HOME_PCT = 55;
    private const ODD_1 = 2.00;
    private const VISITOR_PCT = 50;
    private const ODD_2 = 2.00;

    public function __construct(
        private readonly ReportRepository $reportRepository,
    ) {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $tips = $this->reportRepository->zuluTips(
            self::HOME_PCT,
            self::ODD_1,
            self::VISITOR_PCT,
            self::ODD_2,
        );

        echo "\n";
        echo "Tipster, date, home, visitor, odd_1, odd_2, home_pct, visitor_pct \n";
        echo "------------------------------------------------------------------ \n";

        foreach ($tips as $tip) {
            $isHome = $tip['home_pct'] >= $tip['visitor_pct'];

            $odd1 = $isHome ? "({$tip['odd_1']})" : $tip['odd_1'];
            $homePct = $isHome ? "({$tip['home_pct']})" : $tip['home_pct'];

            $odd2 = !$isHome ? "({$tip['odd_2']})" : $tip['odd_2'];
            $visitorPct = !$isHome ? "({$tip['visitor_pct']})" : $tip['visitor_pct'];

            echo $tip['tipsterName'] . ", ";
            echo $tip['date'] . ", ";
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

