<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName')
            ->add('firstName')
            ->add('email')
            ->add('password')
            ->add('phone_number')
            ->add('address')
            ->add('zipCode')
            ->add('City')
            ->add('Country')
            ->add('registrationDate', null, [
                'widget' => 'single_text'
            ])
            ->add('totalStorageSpace')
            ->add('storageLimit')
            ->add('storageUsed')
            ->add('role')
            ->add('updatedAt', null, [
                'widget' => 'single_text'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
