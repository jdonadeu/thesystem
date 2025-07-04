<?php

namespace App\Command;

use App\Service\FilesystemService;
use App\Service\MatchMerger;
use App\Tipster\ForeBet;
use App\Tipster\PronosticosFutbol365;
use App\Tipster\Zulu;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'main:main')]
class Main
{
    public function __construct(
        private readonly Zulu $zulu,
        private readonly MatchMerger $matchMerger
    ) {
    }

    public function __invoke(): int
    {
        $fileSystem = new FilesystemService();
        $foreBet = new ForeBet();
        $pronosticosFutbol365 = new PronosticosFutbol365();

        // Zulu matches
        $zuluMatches1x2 = $this->zulu->getMatches();
        $fileSystem->saveCsvFile('csv/zulu-1x2.csv', $zuluMatches1x2);

        // ForeBet matches
        $foreBetMatches1x2 = $foreBet->getMatches();
        $foreBetMatchesUnderOver = $foreBet->getUnderOverMatches();
        $foreBetMatchesBts = $foreBet->getBothToScoreMatches();
        $fileSystem->saveCsvFile('csv/forebet-1x2.csv', $foreBetMatches1x2);
        $fileSystem->saveCsvFile('csv/forebet-under-over.csv', $foreBetMatchesUnderOver);
        $fileSystem->saveCsvFile('csv/forebet-bts.csv', $foreBetMatchesBts);

        // PronosticosFutbol365
        $pronosticosFutbol365Matches = $pronosticosFutbol365->getMatches();

        // All matches
        $zuluForeBetMatches1x2 = $this->matchMerger->getMatches($zuluMatches1x2, $foreBetMatches1x2);
        $fileSystem->saveCsvFile('csv/zulu-forebet-1x2.csv', $zuluForeBetMatches1x2);

        // Output
        echo "\n\n";
        echo "****************************************************** \n";
        echo "* \n";
        echo "* System Linea del Tubo FINISHED \n";
        echo "* YAAAAASSTAAAAAAAAAAAAA \n";
        echo "* Suerte y gaceta hipica \n";
        echo "* \n";
        echo "****************************************************** \n";
        echo "\n\n";

        // 1X2
        echo "****************************************************** \n";
        echo "* 1X2 \n";
        echo "****************************************************** \n";

        foreach ($zuluForeBetMatches1x2 as $match) {
            if (($match['totalHomePct'] ?? 0) < 110) {
                continue;
            }

            echo "-- " . implode(",", $match) . "\n";
        }

        // Under over
        echo "\n\n";
        echo "****************************************************** \n";
        echo "* Under over \n";
        echo "****************************************************** \n";

        foreach ($foreBetMatchesUnderOver as $match) {
            if ($match['overPct'] < 90) {
                continue;
            }

            echo "-- " . implode(",", $match) . "\n";
        }

        // Under over
        echo "\n\n";
        echo "****************************************************** \n";
        echo "* BTS \n";
        echo "****************************************************** \n";

        foreach ($foreBetMatchesBts as $match) {
            if ($match['yesPct'] < 90) {
                continue;
            }

            echo "-- " . implode(",", $match) . "\n";
        }

        // Zulu matches ordered by home pct
        echo "\n\n";
        echo "****************************************************** \n";
        echo "* Zulu ordered by homePct\n";
        echo "****************************************************** \n";

        usort($zuluMatches1x2, function ($item1, $item2) {
            return $item2['homePct'] <=> $item1['homePct'];
        });

        foreach ($zuluMatches1x2 as $match) {
            if ($match['homePct'] < 50) {
                continue;
            }
            echo "-- " . implode(",", $match) . "(" . $match['homePct'] . ")\n";
        }

        // Forebet matches ordered by home pct
        echo "\n\n";
        echo "****************************************************** \n";
        echo "* Forebet ordered by homePct\n";
        echo "****************************************************** \n";

        usort($foreBetMatches1x2, function ($item1, $item2) {
            return $item2['homePct'] <=> $item1['homePct'];
        });

        foreach ($foreBetMatches1x2 as $match) {
            if ($match['homePct'] < 50) {
                continue;
            }
            echo "-- " . implode(",", $match) . "(" . $match['homePct'] . ")\n";
        }

        // PronosticosFutbol365 matches ordered by home pct
        echo "\n\n";
        echo "****************************************************** \n";
        echo "* PronosticosFutbol365 ordered by homePct\n";
        echo "****************************************************** \n";

        usort($pronosticosFutbol365Matches, function ($item1, $item2) {
            return $item2['homePct'] <=> $item1['homePct'];
        });

        foreach ($pronosticosFutbol365Matches as $match) {
            if ($match['homePct'] < 50) {
                continue;
            }
            echo "-- " . implode(",", $match) . "(" . $match['homePct'] . ")\n";
        }

        return Command::SUCCESS;
    }
}
