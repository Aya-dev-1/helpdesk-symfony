<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();
        
        // Redirection en fonction du rôle (simple logique de redirection pour le moment)
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_dashboard');
        } elseif ($this->isGranted('ROLE_TECH')) {
            return $this->redirectToRoute('app_tech_dashboard');
        }

        // Dashboard Utilisateur
        
        // Récupérer les statistiques
        $totalTickets = $ticketRepository->count(['author' => $user]);
        $openTickets = $ticketRepository->count(['author' => $user, 'status' => Ticket::STATUS_OPEN]);
        $resolvedTickets = $ticketRepository->count(['author' => $user, 'status' => Ticket::STATUS_RESOLVED]);
        $inProgressTickets = $ticketRepository->count(['author' => $user, 'status' => Ticket::STATUS_IN_PROGRESS]);

        // Derniers tickets
        $latestTickets = $ticketRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('dashboard/user.html.twig', [
            'stats' => [
                'total' => $totalTickets,
                'open' => $openTickets,
                'resolved' => $resolvedTickets,
                'in_progress' => $inProgressTickets,
            ],
            'latest_tickets' => $latestTickets,
        ]);
    }

    #[Route('/admin', name: 'app_admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(): Response
    {
        return $this->render('dashboard/admin.html.twig');
    }

    #[Route('/tech', name: 'app_tech_dashboard')]
    #[IsGranted('ROLE_TECH')]
    public function techDashboard(): Response
    {
        return $this->render('dashboard/tech.html.twig');
    }
}
