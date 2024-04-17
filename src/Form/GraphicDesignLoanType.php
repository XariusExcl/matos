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

class GraphicDesignLoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // dump($options);

        $builder
            ->add('day', ChoiceType::class, [
                'mapped' => false,
                'choices' => $options['days'],
                'label' => 'Jour de l\'emprunt',
            ])
            ->add('comment', TextareaType::class, [
                'mapped' => false,
                'label' => 'Raison de l\'emprunt',
                'required' => true
            ])
            ->add('timeSlot', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Créneau horaire',
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    '9h15 - 12h30' => 'morning',
                    '14h - 17h30' => 'afternoon',
                    '17h30 - 9h15' => 'evening',
                ],
                'required' => true
            ])
            ->add('tablet', EntityType::class, [
                'mapped' => false,
                'class' => Equipment::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Tablette graphique',
                'multiple' => false,
                'expanded' => true,
                'choices' => $options['equipmentCategories']['pen_tablets']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loan::class,
            'equipmentCategories' => null,
            'days' => null,
        ]);
    }
}
