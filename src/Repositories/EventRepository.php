<?php

namespace TheSystem\Repositories;

use mysqli;
use TheSystem\Utils\Db;

class EventRepository
{
    public function __construct(private ?mysqli $conn = null)
    {
        $this->conn = (new Db())->connect();
    }

    public function getByDateAndTeams(string $date, string $homeTeam, string $visitorTeam): ?array
    {
        $sql = "SELECT * FROM events WHERE date = '$date' AND homeTeam = '$homeTeam' AND visitorTeam = '$visitorTeam'";



        return $this->conn->query($sql);
    }
}
