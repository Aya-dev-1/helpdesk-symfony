<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(TicketRepository $ticketRepository): Response
    {
        return $this->render('admin_dashboard/index.html.twig', [
            'totalTickets' => $ticketRepository->countAll(),
            'byStatus' => $ticketRepository->countByStatus(),
            'byPriority' => $ticketRepository->countByPriority(),
            'byTechnician' => $ticketRepository->countByTechnician(),
            'avgResolutionTime' => $ticketRepository->averageResolutionTime(),
        ]);
    }
}
