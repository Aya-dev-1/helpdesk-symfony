<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ticket')]
#[IsGranted('ROLE_ADMIN')]
final class AdminTicketController extends AbstractController
{
    #[Route(name: 'admin_ticket_index', methods: ['GET'])]
    public function index(Request $request, TicketRepository $ticketRepository, UserRepository $userRepository): Response
    {
        // Filters
        $status = $request->query->get('status');
        $priority = $request->query->get('priority');
        $technicianId = $request->query->get('technician');

        $criteria = [];
        if ($status) {
            $criteria['status'] = $status;
        }
        if ($priority) {
            $criteria['priority'] = $priority;
        }
        if ($technicianId) {
            $tech = $userRepository->find($technicianId);
            if ($tech) {
                $criteria['technician'] = $tech;
            }
        }

        // Use findBy for simple filtering
        $tickets = $ticketRepository->findBy($criteria, ['createdAt' => 'DESC']);
        
        // Get technicians for filter
        // Assuming technicians have ROLE_TECHNICIAN or ROLE_ADMIN, or just all users.
        // For simplicity, let's get all users or filter by role if possible.
        // UserRepository might not have findByRole, so I'll just get all for now or check if there is a method.
        $technicians = $userRepository->findAll();

        return $this->render('admin_ticket/index.html.twig', [
            'tickets' => $tickets,
            'technicians' => $technicians,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager, ActivityLogger $logger): Response
    {
        $form = $this->createForm(\App\Form\AdminTicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $logger->log($this->getUser(), 'TICKET_UPDATE', "Ticket #{$ticket->getId()} updated by admin.");

            return $this->redirectToRoute('admin_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager, ActivityLogger $logger): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->getPayload()->getString('_token'))) {
            $ticketId = $ticket->getId();
            $entityManager->remove($ticket);
            $entityManager->flush();
            
            $logger->log($this->getUser(), 'TICKET_DELETE', "Ticket #{$ticketId} deleted by admin.");
        }

        return $this->redirectToRoute('admin_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}
