<?php

class ForeBet
{
    private string $url;

    public function __construct()
    {
        $today = date('Y-m-d');
        $this->url = 'https://www.forebet.com/scripts/getrs.php?ln=es&tp=1x2&in=' . $today . '&ord=0&tz=+60';
    }

    public function getMatches(): array
    {
        $json = file_get_contents('data/forebet.json');

        if (strlen($json) === 0) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            $json = curl_exec($ch);
            curl_close($ch);
        }

        $matches = json_decode($json, true);
        $foreBetMatches = [];
        $now = new DateTime();

        foreach ($matches[0] as $match) {
            $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $match['DATE_BAH']);

            if ($dateTime < $now) {
                continue;
            }

            //var_dump($match);

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
}
