<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Last Name',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('firstName', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'First Name',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('email', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Email',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,  // Ne pas mapper directement à l'entité User
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Laissez vide si vous ne souhaitez pas changer votre mot de passe',
                ],
                'label' => 'Mot de passe',
            ])
            ->add('phone_number', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Phone Number',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('address', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Address',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('zipCode', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Zip Code',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('City', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'City',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('Country', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Country',
                'label_attr' => ['class' => 'form-label'],
            ]);

        if ($options['is_admin']) {
            $builder
                ->add('totalStorageSpace', null, [
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Total Storage Space',
                    'label_attr' => ['class' => 'form-label'],
                ])
                ->add('storageLimit', null, [
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Storage Limit',
                    'label_attr' => ['class' => 'form-label'],
                ])
                ->add('roles', ChoiceType::class, [
                    'choices' => [
                        'Admin' => 'ROLE_ADMIN',
                        'User' => 'ROLE_USER',
                    ],
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'Roles',
                    'label_attr' => ['class' => 'form-label'],
                    'attr' => ['class' => 'form-check-inline'],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_admin' => false,
        ]);
    }
}