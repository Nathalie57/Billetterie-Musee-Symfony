<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Ramsey\Uuid\Uuid;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numberTickets', ChoiceType::class, [
                'label'   => 'Nombre de billets',
                'choices' => [
                    '1'   => 1,
                    '2'   => 2,
                    '3'   => 3,
                    '4'   => 4,
                    '5'   => 5,
                    '6'   => 6,
                    '7'   => 7,
                    '8'   => 8,
                    '9'   => 9,
                    '10'  => 10
                ],
            ])
            ->add('visitDate', DateType::class, [
                'label' => 'Date de la visite',
                'widget' => 'single_text'])
            ->add('visitDuration', ChoiceType::class, [
                'label'   => 'Durée de la visite',
                'choices' => [
                    'Journée'      => 1,
                    'Demi-journée' => .5
                ],
            ])
            ->add('clientName', TextType::class, ['label'   => 'Nom et prénom'])
            ->add('clientAddress', TextType::class, ['label'   => 'Adresse'])
            ->add('clientCountry', CountryType::class, ['label' => 'Pays'])
            ->add('clientEmail', EmailType::class, ['label' => 'Email'])
            
            ->add('Valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}