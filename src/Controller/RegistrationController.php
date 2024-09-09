<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Entity\Transaction;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les informations de paiement
            $cardNumber = $form->get('cardNumber')->getData();
            $expirationDate = $form->get('expirationDate')->getData();
            $cvc = $form->get('cvc')->getData();

            // Valider le paiement
            if (!$this->isPaymentValid($cardNumber, $expirationDate, $cvc)) {
                $this->addFlash('error', 'Le paiement a échoué. Veuillez vérifier les informations de votre carte.');
                return $this->redirectToRoute('app_register');
            }

            // Enregistrer l'utilisateur
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            // Créer la transaction
            $transaction = new Transaction();
            $transaction->setUserTransaction($user);
            $transaction->setAmount(20.00); // Montant fixe de 20€
            $transaction->setTransactionType('Storage Purchase');
            $transaction->setTransactionDate(new \DateTime());
            $transaction->setStatus('completed');

            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('mailtrap@mail.com', 'mailtrap'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Votre compte a été créé et le paiement a été effectué avec succès.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    private function isPaymentValid($cardNumber, $expirationDate, $cvc): bool
    {
        if (!preg_match('/^\d{16}$/', $cardNumber)) {
            $this->addFlash('error', 'Le numéro de carte doit contenir exactement 16 chiffres.');
            return false;
        }

        if (!preg_match('/^\d{2}\/\d{2}$/', $expirationDate)) {
            $this->addFlash('error', 'La date d\'expiration doit être au format MM/YY.');
            return false;
        }

        if (!preg_match('/^\d{3}$/', $cvc)) {
            $this->addFlash('error', 'Le CVC doit contenir exactement 3 chiffres.');
            return false;
        }

        return true;
    }


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
            // Ajouter un message flash de succès pour la vérification de l'email
            $this->addFlash('success', 'Your email address has been verified.');
        } catch (VerifyEmailExceptionInterface $exception) {
            // Ajouter un message flash d'erreur si la vérification échoue
            $this->addFlash('warning', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        return $this->redirectToRoute('app_home');
    }

}
