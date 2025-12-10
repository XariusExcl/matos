<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Loan;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AudiovisualSaeLoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDay', ChoiceType::class, [
                'mapped' => false,
                'choices' => $options['days'],
                'label' => 'Départ de l\'emprunt',
            ])
            ->add('startTimeSlot', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Créneau horaire',
                'choices' => [
                    '9h30' => '0930',
                    '15h' => '1500',
                    '20h30' => '2030'
                ],
                'required' => true
            ])
            ->add('endDay', ChoiceType::class, [
                'mapped' => false,
                'choices' => $options['days'],
                'label' => 'Retour de l\'emprunt',
            ])
            ->add('endTimeSlot', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Créneau horaire',
                'choices' => [
                    '9h30' => '0930',
                    '15h' => '1500',
                    '20h30' => '2030'
                ],
                'required' => true
            ])
            ->add('packs', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Packs',
                'multiple' => false,
                'expanded' => true,
                'choices' => $options['equipmentCategories']['packs'],
                'required' => true
            ])
            ->add('pack_bonus', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Bonus',
                'multiple' => false,
                'expanded' => true,
                'choices' => $options['equipmentCategories']['pack_bonus'],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loan::class,
            'equipmentCategories' => null,
            'accessoryCategories' => null,
            'subCategories' => null,
            'days' => null,
        ]);
    }
}
