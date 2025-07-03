<?php

namespace TheSystem\Entities;

use Exception;
use mysqli;
use TheSystem\Utils\Db;

class Event
{
    public string $date;
    public string $homeTeam;
    public string $visitorTeam;

    public function __construct(private ?mysqli $conn = null)
    {
        $this->conn = (new Db())->connect();
    }

    public function insert(): int
    {
        $sql = "INSERT INTO events (date, homeTeam, visitorTeam) VALUES
                 ('$this->date', '$this->homeTeam', '$this->visitorTeam')";

        try {
            $this->conn->query($sql);
        } catch (Exception $e) {
            if ($e->getCode() !== 1062) {
                throw $e;
            }
        }

        return $this->conn->insert_id;
    }
}
