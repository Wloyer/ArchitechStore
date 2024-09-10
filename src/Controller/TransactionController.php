<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PaymentFormType;
use App\Repository\TransactionRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('ROLE_USER')]  // Vérifie que l'utilisateur est connecté
    public function payment(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le montant du plan et l'espace à ajouter depuis la requête
        $amount = (float) $request->get('price', 20);  // Valeur par défaut à 20€
        $storageToAdd = ($amount / 20) * 20;  // Calculer l'espace à ajouter (20 Go pour chaque tranche de 20€)

        // Calcul de la taxe (par exemple, 20% de taxe)
        $taxRate = 0.20;
        $taxAmount = $amount * $taxRate;
        $totalAmount = $amount + $taxAmount;

        // Créer le formulaire avec le montant dynamique
        $form = $this->createForm(PaymentFormType::class, null, ['amount' => $amount]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créer une nouvelle transaction
            $transaction = new Transaction();
            $transaction->setUserTransaction($this->getUser());  // Associer la transaction à l'utilisateur connecté
            $transaction->setAmount($totalAmount);  // Enregistrer le montant choisi
            $transaction->setTransactionDate(new \DateTime());  // Date de la transaction
            $transaction->setStatus('completed');  // Statut de la transaction (completed)
            $transaction->setTransactionType('CreditCard');  // Type de paiement (par carte)

            // Persister la transaction dans la base de données
            $entityManager->persist($transaction);

            // Ajouter l'espace de stockage à l'utilisateur
            $user = $this->getUser();
            $user->setTotalStorageSpace($user->getTotalStorageSpace() + $storageToAdd);
            $user->setStorageLimit($user->getStorageLimit() + $storageToAdd);

            // Sauvegarder les modifications de l'utilisateur
            $entityManager->persist($user);

            // Créer une nouvelle facture
            $invoice = new Invoice();
            $invoice->setUserInvoice($user); // Associer la facture à l'utilisateur
            $invoice->setTransactionInvoice($transaction); // Associer la transaction à la facture
            $invoice->setAmountWithoutTax($amount);  // Montant sans taxe
            $invoice->setTaxAmount($taxAmount);  // Montant de la taxe
            $invoice->setTotalAmount($totalAmount);  // Montant total (avec la taxe)
            $invoice->setInvoiceDate(new \DateTime()); // Date de la facture

            // Persister la facture dans la base de données
            $entityManager->persist($invoice);

            // Sauvegarder la transaction et la facture
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Paiement effectué avec succès pour ' . number_format($totalAmount, 2) . '€. ' . $storageToAdd . ' Go d\'espace de stockage ont été ajoutés.');

            // Rediriger l'utilisateur vers son profil ou une page de confirmation
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('transaction/payment.html.twig', [
            'paymentForm' => $form->createView(),
            'amount' => $amount,
        ]);
    }
}
