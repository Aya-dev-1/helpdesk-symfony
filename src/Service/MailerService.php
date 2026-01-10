<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerService
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function sendTicketCreated(User $user, Ticket $ticket): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@helpdesk.com', 'HelpDesk Support'))
            ->to($user->getEmail())
            ->subject('Nouveau ticket créé : #' . $ticket->getId())
            ->htmlTemplate('emails/ticket_created.html.twig')
            ->context([
                'user' => $user,
                'ticket' => $ticket,
            ]);

        $this->mailer->send($email);
    }

    public function sendCommentNotification(User $recipient, Ticket $ticket, Comment $comment): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@helpdesk.com', 'HelpDesk Support'))
            ->to($recipient->getEmail())
            ->subject('Nouveau commentaire sur le ticket #' . $ticket->getId())
            ->htmlTemplate('emails/comment_added.html.twig')
            ->context([
                'recipient' => $recipient,
                'ticket' => $ticket,
                'comment' => $comment,
            ]);

        $this->mailer->send($email);
    }
}
