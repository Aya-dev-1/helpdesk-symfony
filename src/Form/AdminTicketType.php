<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description du problème'
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Open' => 'open',
                    'In Progress' => 'in_progress',
                    'Closed' => 'closed',
                ],
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'Low' => 'low',
                    'Medium' => 'medium',
                    'High' => 'high',
                ],
            ])
            ->add('technician', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'placeholder' => 'Choisir un technicien',
                'required' => false,
            ])
            ->add('adminComment', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire Admin (Interne)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
