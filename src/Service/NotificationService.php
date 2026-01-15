<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\TicketComment;
use Psr\Log\LoggerInterface;

class NotificationService
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function notifyAssignment($tech, Ticket $ticket): void
    {
        $name = \is_object($tech) && method_exists($tech, 'getUserIdentifier') ? $tech->getUserIdentifier() : (string) $tech;
        $this->logger->info(sprintf(
            '[NOTIF] Ticket #%d assigné au technicien %s',
            $ticket->getId(),
            $name
        ));
    }

    public function notifyComment($tech, Ticket $ticket, TicketComment $comment): void
    {
        $name = \is_object($tech) && method_exists($tech, 'getUserIdentifier') ? $tech->getUserIdentifier() : (string) $tech;
        $this->logger->info(sprintf(
            '[NOTIF] Nouveau commentaire sur ticket #%d par %s: %s',
            $ticket->getId(),
            $name,
            mb_substr($comment->getContent(), 0, 120)
        ));
    }
}
