<?php

namespace App\Repository;

use App\Entity\ForebetOptimaValues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ForebetOptimalValuesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, ForebetOptimaValues::class);
    }

    public function get(): ForebetOptimaValues {
        return $this->findAll()[0];
    }
}
