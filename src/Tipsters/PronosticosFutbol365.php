<?php

namespace App\Tipsters;

use DOMDocument;
use DOMXPath;

class PronosticosFutbol365
{
    private string $url = 'https://pronosticosfutbol365.com/predicciones-de-futbol/';

    public function getMatches(): array
    {
        $allMatches = [];
        $html = file_get_contents($this->url);

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $competitions = $xpath->query("//div[contains(@class, 'competition')]");

        foreach ($competitions as $competition) {
            $matches = $xpath->query(".//div[contains(@class, 'match')]", $competition);

            foreach ($matches as $match) {
                $dateNode = $xpath->query(".//div[contains(@class, 'date')]", $match);
                if ($dateNode->length === 0) {
                    continue;
                }

                $matchRow = $xpath->query(".//div[contains(@class, 'matchrow')]", $match);
                $teams = $xpath->query(".//div[contains(@class, 'teams')]", $matchRow->item(0));
                $hostTeam = $xpath->query(".//div[contains(@class, 'hostteam')]", $teams->item(0));
                $hostTeamName = $xpath->query(".//div[contains(@class, 'name')]", $hostTeam->item(0));
                $guestTeam = $xpath->query(".//div[contains(@class, 'guestteam')]", $teams->item(0));
                $guestTeamName = $xpath->query(".//div[contains(@class, 'name')]", $guestTeam->item(0));

                $infoRow = $xpath->query(".//div[contains(@class, 'inforow')]", $match);
                $coefRow = $xpath->query(".//div[contains(@class, 'coefrow')]", $infoRow->item(0));
                $coefBox = $xpath->query(".//div[contains(@class, 'coefbox')]", $coefRow->item(0));

                $newMatch['date'] = $dateNode->length > 0 ? trim($dateNode->item(0)->textContent) : "";
                $newMatch['homeTeamName'] = $hostTeamName->item(0)->textContent;
                $newMatch['visitorTeamName'] = $guestTeamName->item(0)->textContent;
                $newMatch['homePct'] = $coefBox->item(11)->textContent;
                $newMatch['drawPct'] = $coefBox->item(12)->textContent;
                $newMatch['visitorPct'] = $coefBox->item(13)->textContent;
                $newMatch['over1.5Pct'] = $coefBox->item(17)->textContent;
                $newMatch['over2.5Pct'] = $coefBox->item(18)->textContent;
                $newMatch['bttsYesPct'] = $coefBox->item(20)->textContent;
                $newMatch['bttsNoPct'] = $coefBox->item(21)->textContent;

                $allMatches[] = $newMatch;
            }
        }

        return $allMatches;
    }
}