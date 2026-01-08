<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByStatus(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.status AS label, COUNT(t.id) AS total')
            ->groupBy('t.status')
            ->getQuery()
            ->getResult();
    }

    public function countByPriority(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.priority AS label, COUNT(t.id) AS total')
            ->groupBy('t.priority')
            ->getQuery()
            ->getResult();
    }

    public function countByTechnician(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.technician AS label, COUNT(t.id) AS total')
            ->groupBy('t.technician')
            ->getQuery()
            ->getResult();
    }

    public function averageResolutionTime(): float
    {
        return (float) $this->createQueryBuilder('t')
            ->select('AVG(TIMESTAMPDIFF(HOUR, t.createdAt, t.resolvedAt))')
            ->where('t.resolvedAt IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
