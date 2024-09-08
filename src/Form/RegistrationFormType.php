<?php
// src/Form/RegistrationFormType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Jean'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre prénom.']),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Dupont'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@domaine.com',
                    'oninvalid' => "this.setCustomValidity('Veuillez entrer une adresse e-mail valide.')",
                    'oninput' => "this.setCustomValidity('')"
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse e-mail.']),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez un mot de passe sécurisé'],
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '06 12 34 56 78',
                    'maxlength' => 10,
                    'pattern' => '[0-9]{10}',
                    'inputmode' => 'numeric',
                    'onkeypress' => "return event.charCode >= 48 && event.charCode <= 57",
                    'oninvalid' => "this.setCustomValidity('Veuillez entrer un numéro de téléphone à 10 chiffres.')",
                    'oninput' => "this.setCustomValidity('')"
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre numéro de téléphone.']),
                    new Regex([
                        'pattern' => '/^[0-9]{10}$/',
                        'message' => 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
                    ]),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control', 'placeholder' => '123 Rue Exemple'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse.']),
                ],
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '75000',
                    'maxlength' => 5,
                    'pattern' => '[0-9]{5}',
                    'inputmode' => 'numeric',
                    'onkeypress' => "return event.charCode >= 48 && event.charCode <= 57",
                    'oninvalid' => "this.setCustomValidity('Veuillez entrer un code postal valide de 5 chiffres.')",
                    'oninput' => "this.setCustomValidity('')"
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre code postal.']),
                    new Regex([
                        'pattern' => '/^[0-9]{5}$/',
                        'message' => 'Le code postal doit contenir exactement 5 chiffres.',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Paris'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre ville.']),
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'attr' => ['class' => 'form-control', 'placeholder' => 'France'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre pays.']),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les termes et conditions',
                'attr' => ['class' => 'form-check-input'],
                'mapped' => false,
                'constraints' => [
                    new IsTrue(['message' => 'Vous devez accepter les termes.']),
                ],
            ])
            ->add('cardNumber', TextType::class, [
                'label' => 'Numéro de carte',
                'mapped' => false, // Ne pas mapper ce champ à l'entité User
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer le numéro de carte']),
                    new Length(['min' => 16, 'max' => 16, 'exactMessage' => 'Le numéro de carte doit être de 16 chiffres']),
                ],
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'Date d\'expiration',
                'mapped' => false, // Ne pas mapper ce champ à l'entité User
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer la date d\'expiration']),
                ],
            ])
            ->add('cvc', TextType::class, [
                'label' => 'CVC',
                'mapped' => false, // Ne pas mapper ce champ à l'entité User
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer le CVC']),
                    new Length(['min' => 3, 'max' => 3, 'exactMessage' => 'Le CVC doit être de 3 chiffres']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
