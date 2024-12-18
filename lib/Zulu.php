<?php

class Zulu {
    private string $url = 'https://es.zulubet.com';

    public function getMatches(): array
    {
        $html = file_get_contents($this->url);
        $table = $this->getTableWithClass($html, 'main_table');
        $tableWithMatches = $this->getTableWithClass($this->getInnerHtml($table), 'content_table');

        $zuluMatches = [];
        $now = new DateTime();

        for ($i = 2; $i < count($tableWithMatches->childNodes); $i++) {
            $newMatch = [];
            $row = $tableWithMatches->childNodes[$i];

            if (count($row->childNodes) < 14) {
                continue;
            }

            // Date
            $date = $row->childNodes[0]->nodeValue;
            $seed = ']]>';
            $start = strpos($date, $seed) + strlen($seed);
            $date = substr($date, $start);

            if (DateTime::createFromFormat("d-m, H:i", $date) < $now) {
                continue;
            }

            $newMatch[] = 'ZULU';
            $newMatch[] = $date;
            $newMatch['teams'] = $row->childNodes[1]->nodeValue;
            $newMatch['homePct'] = str_replace("%", "", $row->childNodes[3]->nodeValue);
            $newMatch['drawPct'] = str_replace("%", "", $row->childNodes[4]->nodeValue);
            $newMatch['awayPct'] = str_replace("%", "", $row->childNodes[5]->nodeValue);
            $newMatch[] = $row->childNodes[9]->nodeValue;
            $newMatch[] = $row->childNodes[10]->nodeValue;
            $newMatch[] = $row->childNodes[11]->nodeValue;
            $newMatch[] = $row->childNodes[12]->nodeValue;
            $newMatch[] = $row->childNodes[7]->nodeValue;

            $zuluMatches[] = $newMatch;
        }

        return $zuluMatches;
    }

    private function getTableWithClass(string $html, string $className): DOMNode
    {
        $html = preg_replace("/&(?!\S+;)/", "&amp;", $html);

        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $finder = new DomXPath($doc);
        $table = $finder->query("//*[contains(@class, '$className')]");

        return $table->item(0);
    }

    private function getInnerHtml($node): string {
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML( $child );
        }

        return $innerHTML;
    }
}
