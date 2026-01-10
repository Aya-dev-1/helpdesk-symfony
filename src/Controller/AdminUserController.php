<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users')]
class AdminUserController extends AbstractController
{
    // 🔹 LISTE
    #[Route('/', name: 'admin_users_index')]
    public function index(UserRepository $repo): Response
    {
        return $this->render('admin_user/index.html.twig', [
            'users' => $repo->findAll(),
        ]);
    }

    // 🔹 AJOUT
    #[Route('/new', name: 'admin_users_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ActivityLogger $logger
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $hasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();
            
            $logger->log($this->getUser(), 'USER_CREATE', "User {$user->getEmail()} created by admin.");

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin_user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 🔹 MODIFICATION
    #[Route('/{id}/edit', name: 'admin_users_edit')]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ActivityLogger $logger
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('plainPassword')->getData()) {
                $password = $hasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                );
                $user->setPassword($password);
            }

            $em->flush();
            
            $logger->log($this->getUser(), 'USER_UPDATE', "User {$user->getEmail()} updated by admin.");

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin_user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    // 🔹 SUPPRESSION
    #[Route('/{id}/delete', name: 'admin_users_delete')]
    public function delete(User $user, EntityManagerInterface $em, ActivityLogger $logger): Response
    {
        $email = $user->getEmail();
        $em->remove($user);
        $em->flush();
        
        $logger->log($this->getUser(), 'USER_DELETE', "User {$email} deleted by admin.");

        return $this->redirectToRoute('admin_users_index');
    }
}
