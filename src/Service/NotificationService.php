<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\TicketComment;
use App\Entity\User;
use Psr\Log\LoggerInterface;

class NotificationService
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function notifyAssignment(User $tech, Ticket $ticket): void
    {
        $this->logger->info(sprintf(
            '[NOTIF] Ticket #%d assigné au technicien %s',
            $ticket->getId(),
            $tech->getUserIdentifier()
        ));
    }

    public function notifyComment(User $tech, Ticket $ticket, TicketComment $comment): void
    {
        $this->logger->info(sprintf(
            '[NOTIF] Nouveau commentaire sur ticket #%d par %s: %s',
            $ticket->getId(),
            $tech->getUserIdentifier(),
            mb_substr($comment->getContent(), 0, 120)
        ));
    }
}

