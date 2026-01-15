<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TECHNICIAN')]
class TechnicianDashboardController extends AbstractController
{
    #[Route('/technician/dashboard', name: 'app_tech_dashboard', methods: ['GET'])]
    public function index(TicketRepository $ticketRepository): Response
    {
        /** @var User $tech */
        $tech = $this->getUser();

        $username = $tech?->getUserIdentifier();
        $tickets = [];
        try {
            $tickets = $ticketRepository->createQueryBuilder('t')
                ->andWhere('t.assignedToUsername = :tech')
                ->setParameter('tech', $username)
                ->orderBy('t.updatedAt', 'DESC')
                ->getQuery()
                ->getResult();
        } catch (\Throwable $e) {}

        $total = \count($tickets);
        $byStatus = [];
        $byPriority = [];
        $resolutionTimes = [];
        $openAges = [];

        foreach ($tickets as $ticket) {
            $status = method_exists($ticket, 'getStatus') ? $ticket->getStatus() : 'inconnu';
            $priority = method_exists($ticket, 'getPriority') ? $ticket->getPriority() : 'inconnu';
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
            $byPriority[$priority] = ($byPriority[$priority] ?? 0) + 1;

            if (method_exists($ticket, 'getResolvedAt') && $ticket->getResolvedAt()) {
                $created = method_exists($ticket, 'getCreatedAt') ? $ticket->getCreatedAt() : null;
                $resolved = $ticket->getResolvedAt();
                if ($created && $resolved) {
                    $resolutionTimes[] = $resolved->getTimestamp() - $created->getTimestamp();
                }
            } else {
                $created = method_exists($ticket, 'getCreatedAt') ? $ticket->getCreatedAt() : null;
                if ($created) {
                    $openAges[] = time() - $created->getTimestamp();
                }
            }
        }

        $avgResolutionSeconds = 0;
        if (\count($resolutionTimes) > 0) {
            $avgResolutionSeconds = (int) (\array_sum($resolutionTimes) / \count($resolutionTimes));
        }
        $avgOpenDays = 0;
        if (\count($openAges) > 0) {
            $avgOpenDays = (int) \round((\array_sum($openAges) / \count($openAges)) / 86400, 1);
        }
        $recentTickets = \array_slice($tickets, 0, 8);

        return $this->render('technician_dashboard/index.html.twig', [
            'total' => $total,
            'byStatus' => $byStatus,
            'byPriority' => $byPriority,
            'avgResolutionSeconds' => $avgResolutionSeconds,
            'avgOpenDays' => $avgOpenDays,
            'recentTickets' => $recentTickets,
        ]);
    }
}
