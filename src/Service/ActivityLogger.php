<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class ActivityLogger
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    public function log(UserInterface $user, string $action, string $description): void
    {
        if (!$user instanceof User) {
            return;
        }

        $log = new ActivityLog();
        $log->setUser($user);
        $log->setAction($action);
        $log->setDescription($description);

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $log->setIpAddress($request->getClientIp());
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
