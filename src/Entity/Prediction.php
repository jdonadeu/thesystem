<?php

namespace App\Entity;

use App\Repository\PredictionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PredictionRepository::class)]
class Prediction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $eventId;

    #[ORM\Column]
    private int $tipsterId;

    #[ORM\Column]
    private ?float $homePct = null;

    #[ORM\Column]
    private ?float $drawPct = null;

    #[ORM\Column]
    private ?float $visitorPct = null;

    #[ORM\Column]
    private ?float $over15Pct = null;

    #[ORM\Column]
    private ?float $over25Pct = null;

    #[ORM\Column]
    private ?float $bttsPct = null;

    #[ORM\Column]
    private ?float $avgGoals = null;

    #[ORM\Column]
    private ?int $homeGoals = null;

    #[ORM\Column]
    private ?int $visitorGoals = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function getTipsterId(): int
    {
        return $this->tipsterId;
    }

    public function setTipsterId(int $tipsterId): void
    {
        $this->tipsterId = $tipsterId;
    }

    public function getHomePct(): ?float
    {
        return $this->homePct;
    }

    public function setHomePct(?float $homePct): void
    {
        $this->homePct = $homePct;
    }

    public function getDrawPct(): ?float
    {
        return $this->drawPct;
    }

    public function setDrawPct(?float $drawPct): void
    {
        $this->drawPct = $drawPct;
    }

    public function getVisitorPct(): ?float
    {
        return $this->visitorPct;
    }

    public function setVisitorPct(?float $visitorPct): void
    {
        $this->visitorPct = $visitorPct;
    }

    public function getOver15Pct(): ?float
    {
        return $this->over15Pct;
    }

    public function setOver15Pct(?float $over15Pct): void
    {
        $this->over15Pct = $over15Pct;
    }

    public function getOver25Pct(): ?float
    {
        return $this->over25Pct;
    }

    public function setOver25Pct(?float $over25Pct): void
    {
        $this->over25Pct = $over25Pct;
    }

    public function getBttsPct(): ?float
    {
        return $this->bttsPct;
    }

    public function setBttsPct(?float $bttsPct): void
    {
        $this->bttsPct = $bttsPct;
    }

    public function getAvgGoals(): ?float
    {
        return $this->avgGoals;
    }

    public function setAvgGoals(?float $avgGoals): void
    {
        $this->avgGoals = $avgGoals;
    }

    public function getHomeGoals(): ?int
    {
        return $this->homeGoals;
    }

    public function setHomeGoals(?int $homeGoals): void
    {
        $this->homeGoals = $homeGoals;
    }

    public function getVisitorGoals(): ?int
    {
        return $this->visitorGoals;
    }

    public function setVisitorGoals(?int $visitorGoals): void
    {
        $this->visitorGoals = $visitorGoals;
    }
}
