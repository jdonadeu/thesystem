<?php

namespace App\Entity;

use App\Repository\ForebetOptimalValuesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForebetOptimalValuesRepository::class)]
#[ORM\Table(name: 'forebet_optimal_values')]
class ForebetOptimaValues
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private float $homeMinPct;

    #[ORM\Column]
    private float $homeMinOdd;

    #[ORM\Column]
    private float $homeMaxOdd;

    #[ORM\Column]
    private float $visitorMinPct;

    #[ORM\Column]
    private float $visitorMinOdd;

    #[ORM\Column]
    private float $visitorMaxOdd;

    public function getHomeMinPct(): float
    {
        return $this->homeMinPct;
    }

    public function getHomeMinOdd(): float
    {
        return $this->homeMinOdd;
    }

    public function getHomeMaxOdd(): float
    {
        return $this->homeMaxOdd;
    }

    public function getVisitorMinPct(): float
    {
        return $this->visitorMinPct;
    }

    public function getVisitorMinOdd(): float
    {
        return $this->visitorMinOdd;
    }

    public function getVisitorMaxOdd(): float
    {
        return $this->visitorMaxOdd;
    }
}
