<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/activity')]
#[IsGranted('ROLE_ADMIN')]
final class AdminActivityController extends AbstractController
{
    #[Route(name: 'admin_activity_index', methods: ['GET'])]
    public function index(ActivityLogRepository $activityLogRepository): Response
    {
        return $this->render('admin_activity/index.html.twig', [
            'logs' => $activityLogRepository->findBy([], ['createdAt' => 'DESC'], 100), // Last 100 logs
        ]);
    }
}
