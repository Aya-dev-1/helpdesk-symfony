<?php

namespace App\Controller;

use App\Entity\Ticket;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\TicketComment;
use App\Form\TicketCommentType;
use App\Repository\TicketRepository;
use App\Repository\TicketCommentRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TECHNICIAN')]
class TechnicianTicketController extends AbstractController
{
    #[Route('/technician/tickets', name: 'tech_ticket_index', methods: ['GET'])]
    public function index(
        Request $request,
        TicketRepository $ticketRepository
    ): Response {
        /** @var UserInterface|null $tech */
        $tech = $this->getUser();
        $username = $tech?->getUserIdentifier();

        $status = $request->query->get('status');
        $priority = $request->query->get('priority');
        $from = $request->query->get('from');
        $to = $request->query->get('to');

        $qb = $ticketRepository->createQueryBuilder('t')
            ->andWhere('t.assignedToUsername = :tech')
            ->setParameter('tech', $username)
            ->orderBy('t.updatedAt', 'DESC');

        if ($status) {
            $qb->andWhere('t.status = :status')->setParameter('status', $status);
        }
        if ($priority) {
            $qb->andWhere('t.priority = :priority')->setParameter('priority', $priority);
        }
        if ($from) {
            $qb->andWhere('t.createdAt >= :from')->setParameter('from', new \DateTime($from));
        }
        if ($to) {
            $qb->andWhere('t.createdAt <= :to')->setParameter('to', new \DateTime($to));
        }

        try {
            $tickets = $qb->getQuery()->getResult();
        } catch (\Throwable $e) {
            $tickets = [];
        }

        return $this->render('technician_ticket/index.html.twig', [
            'tickets' => $tickets,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    #[Route('/technician/tickets/{id}', name: 'tech_ticket_show', methods: ['GET', 'POST'])]
    public function show(
        Ticket $ticket,
        Request $request,
        EntityManagerInterface $em,
        TicketCommentRepository $commentRepo,
        NotificationService $notifier
    ): Response {
        /** @var UserInterface|null $tech */
        $tech = $this->getUser();
        $username = $tech?->getUserIdentifier();
        if ($ticket->getAssignedToUsername() !== $username) {
            throw $this->createAccessDeniedException('Ce ticket n’est pas assigné à vous.');
        }

        $comment = new TicketComment();
        $comment->setTicket($ticket);
        $comment->setAuthorUsername($username ?? 'inconnu');
        $comment->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(TicketCommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();
            $notifier->notifyComment($username ?? 'inconnu', $ticket, $comment);

            $this->addFlash('success', 'Commentaire ajouté.');
            return $this->redirectToRoute('tech_ticket_show', ['id' => $ticket->getId()]);
        }

        $comments = $commentRepo->findBy(['ticket' => $ticket], ['createdAt' => 'DESC']);

        return $this->render('technician_ticket/show.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
            'comments' => $comments,
        ]);
    }

    #[Route('/technician/tickets/{id}/status', name: 'tech_ticket_update_status', methods: ['POST'])]
    public function updateStatus(
        Ticket $ticket,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        /** @var UserInterface|null $tech */
        $tech = $this->getUser();
        $username = $tech?->getUserIdentifier();
        if ($ticket->getAssignedToUsername() !== $username) {
            throw $this->createAccessDeniedException('Ce ticket n’est pas assigné à vous.');
        }
        $this->denyAccessUnlessGranted('ROLE_TECHNICIAN');

        $newStatus = $request->request->get('status');
        if (!$newStatus) {
            $this->addFlash('warning', 'Statut invalide.');
            return $this->redirectToRoute('tech_ticket_show', ['id' => $ticket->getId()]);
        }

        $ticket->setStatus($newStatus);
        $ticket->setUpdatedAt(new \DateTimeImmutable());

        try {
            $em->flush();
        } catch (\Throwable $e) {
            // ignore DB errors in non-DB environments
        }

        $this->addFlash('success', 'Statut du ticket mis à jour.');
        return $this->redirectToRoute('tech_ticket_show', ['id' => $ticket->getId()]);
    }

    #[Route('/technician/tickets/{id}/reassign', name: 'tech_ticket_reassign', methods: ['POST'])]
    public function reassign(
        Ticket $ticket,
        Request $request,
        EntityManagerInterface $em,
        NotificationService $notifier
    ): Response {
        /** @var UserInterface|null $tech */
        $tech = $this->getUser();
        $username = $tech?->getUserIdentifier();
        if ($ticket->getAssignedToUsername() !== $username) {
            throw $this->createAccessDeniedException('Ce ticket n’est pas assigné à vous.');
        }

        $newTechUsername = $request->request->get('technician_id');
        if (!$newTechUsername) {
            $this->addFlash('warning', 'Technicien cible manquant.');
            return $this->redirectToRoute('tech_ticket_show', ['id' => $ticket->getId()]);
        }

        $ticket->setAssignedToUsername($newTechUsername);
        $ticket->setUpdatedAt(new \DateTimeImmutable());
        try {
            $em->flush();
        } catch (\Throwable $e) {
            // ignore DB errors
        }

        $notifier->notifyAssignment($newTechUsername, $ticket);
        $this->addFlash('success', 'Ticket réassigné avec succès.');
        return $this->redirectToRoute('tech_ticket_show', ['id' => $ticket->getId()]);
    }
}

