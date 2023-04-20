<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class Reservation2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('horairedebut', ChoiceType::class, [
                'label' => 'Heure de début',
                'mapped' => false,
                //affiche les valeurs de l'array et non les clés
                'choices' => array_flip($options['horairedebut']),
                'placeholder' => 'Choisissez une heure de début',
            ])
            ->add('horairefin', ChoiceType::class, [
                'label' => 'Heure de fin',
                'mapped' => false,
                'choices' => array_flip($options['horairefin']),
                'placeholder' => 'Choisissez une heure de fin',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'horairedebut' => [],
            'horairefin' => [],
        ]);
    }
}
