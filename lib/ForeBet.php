<?php

// 1x2 url: https://www.forebet.com/scripts/getrs.php?ln=es&tp=1x2&in=2025-02-20&ord=0&tz=+60
// under-over url: https://www.forebet.com/scripts/getrs.php?ln=es&tp=uo&in=2025-02-20&ord=0&tz=+60
// bts url: https://www.forebet.com/scripts/getrs.php?ln=es&tp=bts&in=2025-02-20&ord=0&tz=+60

class ForeBet
{
    public function getMatches(): array
    {
        $json = file_get_contents('data/forebet-1x2.json');
        $matches = json_decode($json, true);
        $foreBetMatches = [];
        $now = new DateTime();

        foreach ($matches[0] as $match) {
            $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $match['DATE_BAH']);

            if ($dateTime < $now) {
                continue;
            }

            $foreBetMatches[] = [
                'FOREBET',
                $match['DATE_BAH'],
                'teams' => trim(preg_replace('/\s\s+/', ' ', $match['HOST_NAME'] . " - " . $match['GUEST_NAME'])),
                'homePct' => $match['Pred_1'],
                'drawPct' => $match['Pred_X'],
                'awayPct' => $match['Pred_2'],
                'goalsavg' => $match['goalsavg'],
                'host_sc_pr' => $match['host_sc_pr'] . '-' . $match['guest_sc_pr'],
            ];
        }

        return $foreBetMatches;
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

        return $foreBetMatches;
    }
}
