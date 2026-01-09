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
            ->select('t.status AS status, COUNT(t.id) AS total')
            ->groupBy('t.status')
            ->getQuery()
            ->getResult();
    }

    public function countByPriority(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.priority AS priority, COUNT(t.id) AS total')
            ->groupBy('t.priority')
            ->getQuery()
            ->getResult();
    }

    // ✅ CORRIGÉ ICI
    public function countByTechnician(): array
    {
        return $this->createQueryBuilder('t')
            ->select('u.email AS technician, COUNT(t.id) AS total')
            ->join('t.technician', 'u')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }

public function averageResolutionTime(): ?float
{
    $tickets = $this->createQueryBuilder('t')
        ->where('t.resolvedAt IS NOT NULL')
        ->getQuery()
        ->getResult();

    if (count($tickets) === 0) {
        return null;
    }

    $totalHours = 0;

    foreach ($tickets as $ticket) {
        $interval = $ticket->getCreatedAt()->diff($ticket->getResolvedAt());
        $hours =
            ($interval->days * 24) +
            $interval->h +
            ($interval->i / 60);

        $totalHours += $hours;
    }

    return round($totalHours / count($tickets), 2);
}
}