<?php

namespace App\Controller;

use App\Entity\NotificationSettings;
use App\Form\NotificationSettingsType;
use App\Repository\NotificationSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/notification')]
#[IsGranted('ROLE_ADMIN')]
final class AdminNotificationController extends AbstractController
{
    #[Route(name: 'admin_notification_index', methods: ['GET'])]
    public function index(NotificationSettingsRepository $notificationSettingsRepository): Response
    {
        return $this->render('admin_notification/index.html.twig', [
            'notification_settings' => $notificationSettingsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_notification_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $notificationSetting = new NotificationSettings();
        $form = $this->createForm(NotificationSettingsType::class, $notificationSetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($notificationSetting);
            $entityManager->flush();

            return $this->redirectToRoute('admin_notification_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_notification/new.html.twig', [
            'notification_setting' => $notificationSetting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_notification_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, NotificationSettings $notificationSetting, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NotificationSettingsType::class, $notificationSetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_notification_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_notification/edit.html.twig', [
            'notification_setting' => $notificationSetting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_notification_delete', methods: ['POST'])]
    public function delete(Request $request, NotificationSettings $notificationSetting, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$notificationSetting->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($notificationSetting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_notification_index', [], Response::HTTP_SEE_OTHER);
    }
}
