<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $date = new \DateTime();
        $date->format('Y-m-d');
        $options['date'] = $date;
        $builder
            ->add('date', DateType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Date',
                'widget' => 'single_text',
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => $date,
                        'message' => 'La date doit être supérieure ou égale à aujourd\'hui',
                    ]),
                ],
            ])
            ->add('salle_reservation', ChoiceType::class, [
                'label' => 'Salle',
                'required' => true,
                'mapped' => false,
                'choices' => $options['salles'],
                'placeholder' => 'Choisissez une salle',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'salles' => [],
        ]);
    }
}
