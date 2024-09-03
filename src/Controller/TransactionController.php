<?php

namespace App\Controller;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PaymentFormType;

class TransactionController extends AbstractController
{
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
}
