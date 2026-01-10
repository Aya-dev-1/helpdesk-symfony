<?php

namespace App\Form;

use App\Entity\NotificationSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('event', null, [
                'label' => 'Événement (ex: ticket_created)',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('enabled', null, [
                'label' => 'Activer la notification',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NotificationSettings::class,
        ]);
    }
}
