<?php

namespace App\Entity;

use App\Repository\ForebetRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForebetRepository::class)]
#[ORM\Table(name: 'forebet_matches')]
class ForebetMatch
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
    private ?float $homePct = null;

    #[ORM\Column]
    private ?float $drawPct = null;

    #[ORM\Column]
    private ?float $visitorPct = null;

    #[ORM\Column]
    private ?float $avgGoals = null;

    #[ORM\Column]
    private ?int $predHomeGoals = null;

    #[ORM\Column]
    private ?int $predVisitorGoals = null;

    #[ORM\Column]
    private ?float $initialOdd_1 = null;

    #[ORM\Column]
    private ?float $initialOdd_x = null;

    #[ORM\Column]
    private ?float $initialOdd_2 = null;

    #[ORM\Column]
    private ?float $initialHomePct = null;

    #[ORM\Column]
    private ?float $initialVisitorPct = null;

    #[ORM\Column]
    protected DateTime $createdAt;

    #[ORM\Column]
    protected ?DateTime $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function getAvgGoals(): ?float
    {
        return $this->avgGoals;
    }

    public function setAvgGoals(?float $avgGoals): void
    {
        $this->avgGoals = $avgGoals;
    }

    public function getPredHomeGoals(): ?int
    {
        return $this->predHomeGoals;
    }

    public function setPredHomeGoals(?int $predHomeGoals): void
    {
        $this->predHomeGoals = $predHomeGoals;
    }

    public function getPredVisitorGoals(): ?int
    {
        return $this->predVisitorGoals;
    }

    public function setPredVisitorGoals(?int $predVisitorGoals): void
    {
        $this->predVisitorGoals = $predVisitorGoals;
    }

    public function getInitialHomePct(): ?float
    {
        return $this->initialHomePct;
    }

    public function setInitialHomePct(?float $initialHomePct): void
    {
        $this->initialHomePct = $initialHomePct;
    }

    public function getInitialVisitorPct(): ?float
    {
        return $this->initialVisitorPct;
    }

    public function setInitialVisitorPct(?float $initialVisitorPct): void
    {
        $this->initialVisitorPct = $initialVisitorPct;
    }

    public function getInitialOdd1(): ?float
    {
        return $this->initialOdd_1;
    }

    public function setInitialOdd1(?float $initialOdd_1): void
    {
        $this->initialOdd_1 = $initialOdd_1;
    }

    public function getInitialOddX(): ?float
    {
        return $this->initialOdd_x;
    }

    public function setInitialOddX(?float $initialOdd_x): void
    {
        $this->initialOdd_x = $initialOdd_x;
    }

    public function getInitialOdd2(): ?float
    {
        return $this->initialOdd_2;
    }

    public function setInitialOdd2(?float $initialOdd_2): void
    {
        $this->initialOdd_2 = $initialOdd_2;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
