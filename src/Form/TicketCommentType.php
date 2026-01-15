<?php

namespace App\Form;

use App\Entity\TicketComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', TextareaType::class, [
            'label' => 'Commentaire',
            'attr' => [
                'rows' => 3,
                'placeholder' => 'Ajouter un commentaire…',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TicketComment::class,
        ]);
    }
}

