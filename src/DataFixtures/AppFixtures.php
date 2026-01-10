<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création des Utilisateurs
        $users = [];
        
        // Admin
        $admin = new User();
        $admin->setEmail('admin@helpdesk.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('System');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password123'));
        $manager->persist($admin);
        $users['admin'] = $admin;

        // Technicien
        $tech = new User();
        $tech->setEmail('tech@helpdesk.com');
        $tech->setFirstName('Amine');
        $tech->setLastName('Technicien');
        $tech->setRoles(['ROLE_TECH']);
        $tech->setPassword($this->passwordHasher->hashPassword($tech, 'password123'));
        $manager->persist($tech);
        $users['tech'] = $tech;

        // Utilisateur Standard 1
        $user1 = new User();
        $user1->setEmail('user1@helpdesk.com');
        $user1->setFirstName('Sara');
        $user1->setLastName('User');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'password123'));
        $manager->persist($user1);
        $users['user1'] = $user1;

        // Utilisateur Standard 2
        $user2 = new User();
        $user2->setEmail('user2@helpdesk.com');
        $user2->setFirstName('Anas');
        $user2->setLastName('User');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'password123'));
        $manager->persist($user2);
        $users['user2'] = $user2;

        // 2. Création des Tickets
        $ticketsData = [
            [
                'title' => 'Problème de connexion Wifi',
                'description' => 'Je n\'arrive pas à me connecter au réseau Wifi du 3ème étage depuis ce matin.',
                'priority' => Ticket::PRIORITY_HIGH,
                'status' => Ticket::STATUS_OPEN,
                'author' => $users['user1'],
                'assignedTo' => null
            ],
            [
                'title' => 'Demande de nouveau clavier',
                'description' => 'La touche Espace de mon clavier ne fonctionne plus très bien.',
                'priority' => Ticket::PRIORITY_LOW,
                'status' => Ticket::STATUS_IN_PROGRESS,
                'author' => $users['user1'],
                'assignedTo' => $users['tech']
            ],
            [
                'title' => 'Erreur lors de l\'export PDF',
                'description' => 'Le logiciel de comptabilité plante quand je clique sur "Exporter".',
                'priority' => Ticket::PRIORITY_URGENT,
                'status' => Ticket::STATUS_RESOLVED,
                'author' => $users['user2'],
                'assignedTo' => $users['tech']
            ],
            [
                'title' => 'Mise à jour Adobe Reader',
                'description' => 'J\'ai besoin de la dernière version pour lire un document.',
                'priority' => Ticket::PRIORITY_MEDIUM,
                'status' => Ticket::STATUS_OPEN,
                'author' => $users['user2'],
                'assignedTo' => null
            ],
            [
                'title' => 'Ecran noir au démarrage',
                'description' => 'Mon PC ne démarre plus, l\'écran reste noir.',
                'priority' => Ticket::PRIORITY_URGENT,
                'status' => Ticket::STATUS_CLOSED,
                'author' => $users['user1'],
                'assignedTo' => $users['tech']
            ],
        ];

        foreach ($ticketsData as $index => $data) {
            $ticket = new Ticket();
            $ticket->setTitle($data['title']);
            $ticket->setDescription($data['description']);
            $ticket->setPriority($data['priority']);
            $ticket->setStatus($data['status']);
            $ticket->setAuthor($data['author']);
            $ticket->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 10) . ' days'));
            
            if ($data['assignedTo']) {
                $ticket->setAssignedTo($data['assignedTo']);
            }

            $manager->persist($ticket);

            // 3. Ajout de commentaires (pour certains tickets)
            if ($index === 1) { // Ticket Clavier (En cours)
                $comment = new Comment();
                $comment->setContent('J\'ai commandé un nouveau clavier, il arrive demain.');
                $comment->setAuthor($users['tech']);
                $comment->setTicket($ticket);
                $comment->setCreatedAt(new \DateTimeImmutable('-1 day'));
                $manager->persist($comment);
                
                $comment2 = new Comment();
                $comment2->setContent('Merci beaucoup !');
                $comment2->setAuthor($users['user1']);
                $comment2->setTicket($ticket);
                $comment2->setCreatedAt(new \DateTimeImmutable('-2 hours'));
                $manager->persist($comment2);
            }

            if ($index === 2) { // Ticket PDF (Résolu)
                $comment = new Comment();
                $comment->setContent('Le problème venait d\'une mise à jour Windows manquante. C\'est corrigé.');
                $comment->setAuthor($users['tech']);
                $comment->setTicket($ticket);
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}
