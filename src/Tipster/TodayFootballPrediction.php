<?php

namespace App\Tipster;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TodayFootballPrediction
{
    private const HTTP_CLIENT_OPTIONS = [
        'headers' => [
            'x-rapidapi-host' => 'today-football-prediction.p.rapidapi.com',
            'x-rapidapi-key' => '5a3b0d3efemsh0554eb99bd4ff00p1e2151jsnc3c146a240e6'
        ],
    ];

    private HttpClientInterface $httpClient;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
    }

    public function import(string $date): void
    {
        $page = 1;
        echo "Importing matches[date=$date, page=$page] \n";
        $predictions = $this->getPredictions($date, $page);
        $this->importMatches($predictions['matches']);

        $pagination = $predictions['pagination'];
        $lastPage = ceil($pagination['no_of_docs_total'] / $pagination['no_of_docs_in_page']);
        sleep(1);

        for ($page = 2; $page <= $lastPage; $page++) {
            echo "Importing matches[date=$date, page=$page] \n";
            $predictions = $this->getPredictions($date, $page);
            $this->importMatches($predictions['matches']);
            sleep(1);
        }
    }

    private function importMatches(array $matches): void
    {
        foreach ($matches as $match) {
            echo "--- ";
            echo "$match[home_team], ";
            echo "$match[away_team], ";
            echo "$match[date_time], ";
            echo "$match[prediction], ";
            echo "$match[prediction_odd], ";
            echo "$match[prediction_probability], ";
            echo "$match[result_score], ";
            echo "\n";
        }

        echo "\n\n";
    }

    private function getPredictions(string $date, int $page): array
    {
        $response = $this->httpClient->request(
            'GET',
            "https://today-football-prediction.p.rapidapi.com/predictions/list?page=$page&date=$date",
            self::HTTP_CLIENT_OPTIONS,
        );

        return $response->toArray();
    }
}