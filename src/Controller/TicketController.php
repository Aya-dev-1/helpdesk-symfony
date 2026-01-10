<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Form\CommentType;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ticket')]
#[IsGranted('ROLE_USER')]
class TicketController extends AbstractController
{
    #[Route('/', name: 'app_ticket_index', methods: ['GET'])]
    public function index(TicketRepository $ticketRepository): Response
    {
        // L'utilisateur ne voit que ses propres tickets
        $user = $this->getUser();
        $tickets = $ticketRepository->findBy(['author' => $user], ['createdAt' => 'DESC']);

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/new', name: 'app_ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, MailerService $mailer): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setAuthor($this->getUser());
            
            // Gestion de l'upload de fichier
            $attachmentFile = $form->get('attachment')->getData();

            if ($attachmentFile) {
                $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$attachmentFile->guessExtension();

                try {
                    $attachmentFile->move(
                        $this->getParameter('attachments_directory'),
                        $newFilename
                    );
                    $ticket->setAttachment($newFilename);
                } catch (FileException $e) {
                    // ... gérer l'exception si quelque chose se passe mal pendant l'upload du fichier
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier.');
                }
            }

            $entityManager->persist($ticket);
            $entityManager->flush();

            // Envoi de l'email de confirmation
            $mailer->sendTicketCreated($this->getUser(), $ticket);

            $this->addFlash('success', 'Votre ticket a été créé avec succès.');

            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Ticket $ticket, EntityManagerInterface $entityManager, MailerService $mailer): Response
    {
        // Vérifier que l'utilisateur est bien l'auteur du ticket ou a les droits nécessaires
        if ($ticket->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_TECH') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce ticket.');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser());
            $comment->setTicket($ticket);
            
            $entityManager->persist($comment);
            $entityManager->flush();

            // Notifications Email
            $currentUser = $this->getUser();
            $ticketAuthor = $ticket->getAuthor();
            $assignedTech = $ticket->getAssignedTo();

            // Si l'auteur du commentaire n'est pas l'auteur du ticket, on notifie l'auteur du ticket
            if ($currentUser !== $ticketAuthor) {
                $mailer->sendCommentNotification($ticketAuthor, $ticket, $comment);
            }

            // Si l'auteur du commentaire est l'auteur du ticket, on notifie le technicien assigné (s'il y en a un)
            if ($currentUser === $ticketAuthor && $assignedTech) {
                $mailer->sendCommentNotification($assignedTech, $ticket, $comment);
            }

            $this->addFlash('success', 'Votre commentaire a été ajouté.');

            return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getId()]);
        }

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
            'comment_form' => $form,
        ]);
    }
}
