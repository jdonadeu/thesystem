<?php

namespace App\Entity;

use App\Repository\TodayFootballPredictionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TodayFootballPredictionRepository::class)]
#[ORM\Table(name: 'TodayFootballPrediction_matches')]
class TodayFootballPredictionMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $date;

    #[ORM\Column]
    private string $time;

    #[ORM\Column]
    private string $homeTeam;

    #[ORM\Column]
    private string $visitorTeam;

    #[ORM\Column]
    private string $prediction;

    #[ORM\Column]
    private float $initialOdd;

    #[ORM\Column]
    private float $odd;

    #[ORM\Column]
    private float $initialPct;

    #[ORM\Column]
    private float $pct;

    #[ORM\Column]
    private ?int $homeGoals = null;

    #[ORM\Column]
    private ?int $visitorGoals = null;

    #[ORM\Column]
    private float $stake;

    #[ORM\Column]
    private float $bet;

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    public function getHomeTeam(): string
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(string $homeTeam): void
    {
        $this->homeTeam = $homeTeam;
    }

    public function getVisitorTeam(): string
    {
        return $this->visitorTeam;
    }

    public function setVisitorTeam(string $visitorTeam): void
    {
        $this->visitorTeam = $visitorTeam;
    }

    public function getPrediction(): string
    {
        return $this->prediction;
    }

    public function setPrediction(string $prediction): void
    {
        $this->prediction = $prediction;
    }

    public function getInitialOdd(): float
    {
        return $this->initialOdd;
    }

    public function setInitialOdd(float $initialOdd): void
    {
        $this->initialOdd = $initialOdd;
    }

    public function getOdd(): float
    {
        return $this->odd;
    }

    public function setOdd(float $odd): void
    {
        $this->odd = $odd;
    }

    public function getInitialPct(): float
    {
        return $this->initialPct;
    }

    public function setInitialPct(float $initialPct): void
    {
        $this->initialPct = $initialPct;
    }

    public function getPct(): float
    {
        return $this->pct;
    }

    public function setPct(float $pct): void
    {
        $this->pct = $pct;
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

    public function getStake(): float
    {
        return $this->stake;
    }

    public function setStake(float $stake): void
    {
        $this->stake = $stake;
    }

    public function getBet(): float
    {
        return $this->bet;
    }

    public function setBet(float $bet): void
    {
        $this->bet = $bet;
    }
}
