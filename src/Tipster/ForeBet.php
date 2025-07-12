<?php

namespace App\Tipster;

use DateTime;

// 1x2 url: https://www.forebet.com/scripts/getrs.php?ln=es&tp=1x2&in=2025-02-20&ord=0&tz=+60
// under-over url: https://www.forebet.com/scripts/getrs.php?ln=es&tp=uo&in=2025-02-20&ord=0&tz=+60
// bts url: https://www.forebet.com/scripts/getrs.php?ln=es&tp=bts&in=2025-02-20&ord=0&tz=+60
class ForeBet extends Tipster
{
    private const TIPSTER_ID = 2;
    private const TIPSTER_NAME = 'FOREBET';
    private const DATA_FILE = 'data/forebet-1x2.json';
    private const IMPORT_FILE = 'csv/import-forebet.csv';

    public function getMatches(): array
    {
        $json = file_get_contents(self::DATA_FILE);
        $matches = json_decode($json, true);
        $foreBetMatches = [];

        foreach ($matches[0] as $match) {
            $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $match['DATE_BAH']);
            $teams = trim(preg_replace('/\s\s+/', ' ', $match['HOST_NAME'] . " - " . $match['GUEST_NAME']));
            $teamParts = explode("-", $teams);

            if ($match['Pred_1'] < self::WINNING_PCT_THRESHOLD && $match['Pred_2'] < self::WINNING_PCT_THRESHOLD) {
                continue;
            }

            $newMatch = [];
            $newMatch['date'] = $dateTime->format('Y-m-d');
            $newMatch['time'] = $dateTime->format('H:i');
            $newMatch['homeTeam'] = trim($teamParts[0]);
            $newMatch['visitorTeam'] = trim($teamParts[1]);
            $newMatch['homePct'] = $match['Pred_1'];
            $newMatch['drawPct'] = $match['Pred_X'];
            $newMatch['visitorPct'] = $match['Pred_2'];
            $newMatch['goalsavg'] = $match['goalsavg'];
            $newMatch['host_sc_pr'] = $match['host_sc_pr'] . '-' . $match['guest_sc_pr'];
            $newMatch['odd_1'] = $match['best_odd_1'];
            $newMatch['odd_X'] = $match['best_odd_X'];
            $newMatch['odd_2'] = $match['best_odd_2'];

            $foreBetMatches[] = $newMatch;
        }

        return $foreBetMatches;
    }

    public function importMatches(): void
    {
        $matches = $this->getMatches();
        echo self::TIPSTER_NAME . ": Importing " . count($matches) . " matches\n";
        $this->filesystemService->saveCsvFile(self::IMPORT_FILE, $matches);
    }

    public function persistMatches(bool $commit): void
    {
        if (!($handle = fopen(self::IMPORT_FILE, 'r'))) {
            echo "Could not open file " . self::IMPORT_FILE;
            return;
        }

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $goals = explode("-", $row[7]);

            $date = $row[0];
            $homeTeam = $row[1];
            $visitorTeam = $row[2];
            $homePct = $row[3];
            $drawPct = $row[4];
            $visitorPct = $row[5];
            $goalsAvg = $row[6];
            $homeGoals = $goals[0];
            $visitorGoals = $goals[1];

            $event = $this->getEvent(
                $commit,
                self::TIPSTER_NAME,
                $date,
                $homeTeam,
                $visitorTeam,
            );

            if ($commit) {
                $this->predictionRepository->create(
                    $event->getId(),
                    self::TIPSTER_ID,
                    $homePct,
                    $drawPct,
                    $visitorPct,
                    $goalsAvg,
                    $homeGoals,
                    $visitorGoals
                );
            }
        }

        fclose($handle);
    }

    public function getUnderOverMatches(): array
    {
        $json = file_get_contents('data/forebet-under-over.json');
        $matches = json_decode($json, true);
        $foreBetMatches = [];
        $now = new DateTime();

        foreach ($matches[0] as $match) {
            $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $match['DATE_BAH']);

            if ($dateTime < $now) {
                continue;
            }

            $foreBetMatches[] = [
                'FOREBET OVER UNDER 2.5',
                $match['DATE_BAH'],
                'teams' => trim(preg_replace('/\s\s+/', ' ', $match['HOST_NAME'] . " - " . $match['GUEST_NAME'])),
                'underPct' => $match['pr_under'],
                'overPct' => $match['pr_over'],
                'goalsAvg' => $match['goalsavg'],
                'host_sc_pr' => $match['host_sc_pr'] . '-' . $match['guest_sc_pr'],
            ];
        }

        usort($foreBetMatches, function($a, $b) {
            return $a['underPct'] - $b['underPct'];
        });

        return $foreBetMatches;
    }

    public function getBothToScoreMatches(): array
    {
        $json = file_get_contents('data/forebet-bts.json');
        $matches = json_decode($json, true);
        $foreBetMatches = [];
        $now = new DateTime();

        foreach ($matches[0] as $match) {
            $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $match['DATE_BAH']);

            if ($dateTime < $now) {
                continue;
            }

            $foreBetMatches[] = [
                'FOREBET BTS',
                $match['DATE_BAH'],
                'teams' => trim(preg_replace('/\s\s+/', ' ', $match['HOST_NAME'] . " - " . $match['GUEST_NAME'])),
                'noPct' => $match['Pred_no_gg'],
                'yesPct' => $match['Pred_gg'],
                'host_sc_pr' => $match['host_sc_pr'] . '-' . $match['guest_sc_pr'],
            ];
        }

        usort($foreBetMatches, function($a, $b) {
            return $a['noPct'] - $b['noPct'];
        });

        return $foreBetMatches;
    }
}
