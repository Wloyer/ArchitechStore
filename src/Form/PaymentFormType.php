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
        $builder
            ->add('cardNumber', TextType::class, [
                'label' => 'Card Number',
            ])
            ->add('cardHolderName', TextType::class, [
                'label' => 'Card Holder Name',
            ])
            ->add('expiryDate', TextType::class, [
                'label' => 'Expiry Date',
            ])
            ->add('cvv', TextType::class, [
                'label' => 'CVV',
            ])
            ->add('acceptTerms', CheckboxType::class, [
                'label' => 'I accept the terms and conditions',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must accept the terms and conditions to proceed.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Pay 20€',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
