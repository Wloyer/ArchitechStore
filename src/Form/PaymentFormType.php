<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class PaymentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer le montant depuis les options
        $amount = $options['amount'] ?? 20;  // Valeur par défaut si non spécifiée

        $builder
            ->add('cardNumber', TextType::class, [
                'label' => 'Numéro de carte',
                'attr' => ['class' => 'form-control', 'placeholder' => '1234 5678 9012 3456'],
            ])
            ->add('cardHolderName', TextType::class, [
                'label' => 'Nom du titulaire',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Jean Dupont'],
            ])
            ->add('expiryDate', TextType::class, [
                'label' => 'Date d\'expiration (MM/YY)',
                'attr' => ['class' => 'form-control', 'placeholder' => '12/23'],
            ])
            ->add('cvv', TextType::class, [
                'label' => 'CVC',
                'attr' => ['class' => 'form-control', 'placeholder' => '123'],
            ])
            ->add('acceptTerms', CheckboxType::class, [
                'label' => 'J\'accepte les termes et conditions',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les termes et conditions pour continuer.',
                    ]),
                ],
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Payer ' . number_format($amount, 2) . '€',
                'attr' => ['class' => 'btn btn-primary btn-lg btn-block'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'amount' => 20,  // Montant par défaut si non fourni
        ]);
    }
}
