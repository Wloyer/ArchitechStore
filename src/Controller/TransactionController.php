<?php

namespace App\Controller;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PaymentFormType;
use App\Repository\TransactionRepository;
use Symfony\Component\Form\FormFactoryInterface;

#[Route('/transaction')]
class TransactionController extends AbstractController
{

    #[Route('/', name: 'app_file_transaction', methods: ['GET'])]
    public function index(TransactionRepository $transactionRepository): Response
    {
        return $this->render('transaction/index.html.twig', [
            'transaction' => $transactionRepository->findAll(),
        ]);
    }

    #[Route('/transaction{id}', name: 'app_transaction')]
    public function transaction(Transaction $transaction, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaymentFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        // Simuler un paiement réussi
        $transaction->setStatus('completed'); // Modifier le statut à 'completed'
        $entityManager->flush();

        // Rediriger vers la page de succès
        return $this->redirectToRoute('app_transaction_success');
    }
        return $this->render('transaction/payment.html.twig', [
            'paymentForm' => $form->createView(),
        ]);
    }

    #[Route('/transaction/success', name: 'app_transaction_success')]
    public function paymentSuccess(): Response
    {
        return $this->render('transaction/success.html.twig', [
            'message' => 'Your payment was successful. You now have 20GB of storage available.',
        ]);
    }
    #[Route('/payment', name: 'app_payment')]
    public function payment(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le montant du plan depuis la requête
        $amount = $request->get('price', 20);  // Valeur par défaut si non spécifiée

        // Créer le formulaire avec le montant dynamique
        $form = $this->createForm(PaymentFormType::class, null, ['amount' => $amount]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créer une nouvelle transaction et enregistrer le montant
            $transaction = new Transaction();
            $transaction->setUserTransaction($this->getUser());  // Associer la transaction à l'utilisateur connecté
            $transaction->setAmount($amount);  // Enregistrer le montant choisi
            $transaction->setTransactionDate(new \DateTime());  // Date de la transaction
            $transaction->setStatus('completed');  // Statut de la transaction (completed)


            $transaction->setTransactionType('CreditCard');

            // Persister et flush dans la base de données
            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Paiement effectué avec succès pour ' . number_format($amount, 2) . '€.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('transaction/payment.html.twig', [
            'paymentForm' => $form->createView(),
            'amount' => $amount,
        ]);
    }
}
