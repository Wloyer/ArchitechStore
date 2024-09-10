<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Form\InvoiceType;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/invoice')]
#[IsGranted('ROLE_USER')]
class InvoiceController extends AbstractController
{
    #[Route('/', name: 'app_invoice_index', methods: ['GET'])]
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoiceRepository->findAll(),
        ]);
    }

    #[Route('/{id}/download', name: 'app_invoice_download', methods: ['GET'])]
    public function download(Invoice $invoice): Response
    {
        // Vérification que la facture a bien un utilisateur associé
        $user = $invoice->getUserInvoice();
        if (!$user) {
            throw $this->createNotFoundException('Aucun utilisateur associé à cette facture.');
        }

        // Options pour DomPDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // Rendu du template avec les données de la facture et de l'utilisateur
        $html = $this->renderView('invoice/pdf.html.twig', [
            'invoice' => $invoice,
            'user' => $user,  // Passe l'utilisateur à la vue Twig pour afficher ses informations
        ]);

        // Charger le contenu HTML dans DomPDF
        $dompdf->loadHtml($html);

        // Configurer le format de la page
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le fichier PDF
        $dompdf->render();

        // Générer le nom du fichier PDF
        $pdfFileName = 'facture_' . $invoice->getId() . '.pdf';

        // Retourner la réponse pour téléchargement
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $pdfFileName . '"',
        ]);
    }

    #[Route('/my-invoices', name: 'app_my_invoices', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // Seuls les utilisateurs connectés peuvent accéder
    public function myInvoices(InvoiceRepository $invoiceRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer les factures de cet utilisateur
        $invoices = $invoiceRepository->findBy(['userInvoice' => $user]);

        return $this->render('invoice/my_invoices.html.twig', [
            'invoices' => $invoices,
        ]);
    }
    /* 
    #[Route('/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
    } */
}
