<?php

namespace App\Command;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:load-sample-data', description: 'Insère des données de test simples pour Ticket')]
class LoadSampleDataCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $now = new \DateTimeImmutable();
        $samples = [
            ['Problème Wi-Fi', 'ouvert', 'moyenne', -1, null],
            ['Écran bleu au démarrage', 'en_cours', 'haute', -2, null],
            ['Mise à jour Windows bloquée', 'en_attente', 'moyenne', -3, null],
            ['Imprimante ne répond plus', 'résolu', 'faible', -7, -5],
            ['Mot de passe oublié', 'fermé', 'faible', -10, -9],
            ['PC surchauffe', 'en_cours', 'urgente', -1, null],
            ['Réseau très lent', 'ouvert', 'moyenne', -4, null],
            ['Logiciel ne se lance pas', 'en_attente', 'haute', -6, null],
            ['Clavier ne fonctionne pas', 'résolu', 'moyenne', -8, -7],
            ['Souris défectueuse', 'fermé', 'faible', -12, -11],
            ['VPN impossible à connecter', 'ouvert', 'haute', -2, null],
            ['Email non synchronisé', 'en_cours', 'moyenne', -5, null],
        ];

        $count = 0;
        foreach ($samples as [$title, $status, $priority, $createdOffsetDays, $resolvedOffsetDays]) {
            $t = new Ticket();
            $t->setTitle($title);
            $t->setStatus($status);
            $t->setPriority($priority);
            $t->setAssignedToUsername('tech@helpdesk.com');
            $created = $now->modify(sprintf('%d days', $createdOffsetDays));
            $t->setCreatedAt($created);
            $t->setUpdatedAt($created);
            if ($resolvedOffsetDays !== null) {
                $resolved = $now->modify(sprintf('%d days', $resolvedOffsetDays));
                $t->setResolvedAt($resolved);
                $t->setUpdatedAt($resolved);
            }
            $this->em->persist($t);
            $count++;
        }
        $this->em->flush();

        $io->success(sprintf('%d tickets de test insérés.', $count));
        return Command::SUCCESS;
    }
}
