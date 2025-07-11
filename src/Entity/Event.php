<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $tipsterId;

    #[ORM\Column]
    private string $date;

    #[ORM\Column]
    private string $time;

    #[ORM\Column]
    private string $homeTeam;

    #[ORM\Column]
    private string $visitorTeam;

    #[ORM\Column]
    private ?int $homeGoals = null;

    #[ORM\Column]
    private ?int $visitorGoals = null;

    #[ORM\Column]
    private float $odd_1 = 0;

    #[ORM\Column]
    private float $odd_x = 0;

    #[ORM\Column]
    private float $odd_2 = 0;

    #[ORM\Column]
    private float $oddOver15 = 0;

    #[ORM\Column]
    private float $oddOver25 = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTipsterId(): int
    {
        return $this->tipsterId;
    }

    public function setTipsterId(int $tipsterId): void
    {
        $this->tipsterId = $tipsterId;
    }

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

    public function getOdd1(): float
    {
        return $this->odd_1;
    }

    public function setOdd1(float $odd_1): void
    {
        $this->odd_1 = $odd_1;
    }

    public function getOddX(): float
    {
        return $this->odd_x;
    }

    public function setOddX(float $odd_x): void
    {
        $this->odd_x = $odd_x;
    }

    public function getOdd2(): float
    {
        return $this->odd_2;
    }

    public function setOdd2(float $odd_2): void
    {
        $this->odd_2 = $odd_2;
    }

    public function getOddOver15(): float
    {
        return $this->oddOver15;
    }

    public function setOddOver15(float $oddOver15): void
    {
        $this->oddOver15 = $oddOver15;
    }

    public function getOddOver25(): float
    {
        return $this->oddOver25;
    }

    public function setOddOver25(float $oddOver25): void
    {
        $this->oddOver25 = $oddOver25;
    }
}
