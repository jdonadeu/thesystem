<?php

namespace TheSystem\Entities;

use Exception;
use mysqli;
use TheSystem\Utils\Db;

class Prediction
{
    public int $eventId;
    public int $tipsterId;
    public float $homePct;
    public float $drawPct;
    public float $visitorPct;

    public function __construct(private ?mysqli $conn = null)
    {
        $this->conn = (new Db())->connect();
    }

    public function insert(): int
    {
        $sql = "INSERT INTO predictions (eventId, tipsterId, homePct, drawPct, visitorPct) VALUES 
                ($this->eventId, $this->tipsterId, $this->homePct, $this->drawPct, $this->visitorPct)";

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
