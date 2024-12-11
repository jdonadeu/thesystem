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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $json = curl_exec($ch);
        curl_close($ch);

        $matches = json_decode($json, true);
        $foreBetMatches = [];

        foreach ($matches[0] as $match) {
            $foreBetMatches[] = [
                'FOREBET',
                $match['DATE_BAH'],
                trim(preg_replace('/\s\s+/', ' ', $match['HOST_NAME'] . " - " . $match['GUEST_NAME'])),
                $match['Pred_1'],
                $match['Pred_X'],
                $match['Pred_2'],
                $match['best_odd_1'],
                $match['best_odd_X'],
                $match['best_odd_2'],
            ];
        }

        return $foreBetMatches;
    }
}
