<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(TicketRepository $ticketRepo): Response
    {
        return $this->render('admin_dashboard/index.html.twig', [
            'totalTickets' => $ticketRepo->countAll(),
            'ticketsByStatus' => $ticketRepo->countByStatus(),
            'ticketsByPriority' => $ticketRepo->countByPriority(),
            'ticketsByTechnician' => $ticketRepo->countByTechnician(),
            'avgResolutionTime' => $ticketRepo->averageResolutionTime(),
        ]);
    }
}
