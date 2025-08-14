<?php

namespace App\Tipster;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiFootball
{
    private const HTTP_CLIENT_OPTIONS = [
        'headers' => [
            'x-rapidapi-host' => 'api-football-v1.p.rapidapi.com',
            'x-rapidapi-key' => '5a3b0d3efemsh0554eb99bd4ff00p1e2151jsnc3c146a240e6'
        ],
    ];

    private HttpClientInterface $httpClient;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
    }

    public function go()
    {
        $response = $this->httpClient->request(
            'GET',
            'https://api-football-v1.p.rapidapi.com/v3/odds?date=2025-08-11&bookmaker=8',
            self::HTTP_CLIENT_OPTIONS,
        );

        $content = $response->toArray();

        $matches = $content['response'];

        foreach ($matches as $match) {
            $odd1 = null;
            $oddX = null;
            $odd2 = null;

            $fixtureId = $match['fixture']['id'];
            $bets = $match['bookmakers'][0]['bets'];

            foreach ($bets as $bet) {
                if ($bet['name'] !== 'Match Winner') {
                    continue;
                }

                foreach ($bet['values'] as $value) {
                    if ($value['value'] === 'Home') {
                        $odd1 = $value['odd'];
                    } elseif ($value['value'] === 'Draw') {
                        $oddX = $value['odd'];
                    } elseif ($value['value'] === 'Away') {
                        $odd2 = $value['odd'];
                    }
                }

                $predictions = $this->getPredictions($fixtureId);

                echo "$fixtureId, $odd1, $oddX, $odd2  - ($predictions[home], $predictions[draw], $predictions[away]) \n";
            }
        }
    }

    private function getPredictions(int $fixtureId): array
    {
        $response = $this->httpClient->request(
            'GET',
            "https://api-football-v1.p.rapidapi.com/v3/predictions?fixture=$fixtureId",
            self::HTTP_CLIENT_OPTIONS,
        );

        $content = $response->toArray();
        $predictions = $content['response'];
        $percentages = $predictions[0]['predictions']['percent'];

        return [
            'home' => substr($percentages['home'], 0, -1),
            'draw' => substr($percentages['draw'], 0, -1),
            'away' => substr($percentages['away'], 0, -1),
        ];
    }
}