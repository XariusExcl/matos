<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Loan;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AudiovisualLoanType extends AbstractType
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
                    '11h' => '1100',
                    '12h30' => '1230',
                    '14h' => '1400',
                    '15h30' => '1530',
                    '17h00' => '1700',
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
                    '11h' => '1100',
                    '12h30' => '1230',
                    '14h' => '1400',
                    '15h30' => '1530',
                    '17h00' => '1700',
                ],
                'required' => true
            ])
            ->add('comment', TextareaType::class, [
                'mapped' => false,
                'label' => 'Raison de l\'emprunt',
                'required' => true
            ])
            ->add('cameras', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Boîtier',
                'multiple' => false,
                'expanded' => true,
                'choices' => $options['equipmentCategories']['cameras'],
                'required' => false
            ])
            ->add('lenses', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Objectifs',
                'multiple' => false,
                'expanded' => true,
                'required' => false,
                'choices' => $options['equipmentCategories']['lenses'],
            ])
            ->add('microphones', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'label' => 'Micros',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices' => $options['equipmentCategories']['microphones']
            ])
            ->add('lights', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'label' => 'Lumières',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices' => $options['equipmentCategories']['lights']
            ])
            ->add('tripods', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'label' => 'Trépied',
                'multiple' => false,
                'expanded' => true,
                'required' => false,
                'choices' => $options['equipmentCategories']['tripods']
            ])
            ->add('stabilizers', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'label' => 'Stabilisateurs',
                'multiple' => false,
                'expanded' => true,
                'required' => false,
                'choices' => $options['equipmentCategories']['stabilizers']
            ])
            // Extra accessories
            ->add('batteries', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Batteries supplémentaires',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices' => $options['equipmentCategories']['batteries'],
            ])
            /*
            ->add('comment', TextareaType::class, [
                'label' => 'Informations supplémentaires',
                'required' => false,
            ])
            */
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
