<?php

namespace App\Controller;

use App\Repository\FileRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;


#[Route('/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(Request $request, TransactionRepository $transactionRepository, UserRepository $userRepository, FileRepository $fileRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Access Denied: You do not have the required permissions to access this page.');
            return $this->redirectToRoute('app_home');
        }

        // Statistiques des transactions (comme avant)
        $transactions = $transactionRepository->findAll();
        $dates = [];
        $transactionsPerDate = [];
        $amountsPerDate = [];

        $transactions = $transactionRepository->findAll();
        $dates = [];
        $transactionsPerDate = [];
        $amountsPerDate = [];

        foreach ($transactions as $transaction) {
            $date = $transaction->getTransactionDate()->format('Y-m-d');
            if (!isset($transactionsPerDate[$date])) {
                $transactionsPerDate[$date] = 0;
                $amountsPerDate[$date] = 0;
                $dates[] = $date;
            }
            $transactionsPerDate[$date]++;
            $amountsPerDate[$date] += $transaction->getAmount();
        }

        // Récupérer les utilisateurs et les statistiques associées
        $totalUsers = $userRepository->count([]);
        $verifiedUsers = $userRepository->count(['isVerified' => true]);

        // Récupérer le nombre d'inscriptions par mois
        $users = $userRepository->findAll();
        $userRegistrationData = [];
        $totalStorage = 0;
        $usedStorage = 0;

        foreach ($users as $user) {
            $registrationMonth = $user->getRegistrationDate()->format('Y-m');
            if (!isset($userRegistrationData[$registrationMonth])) {
                $userRegistrationData[$registrationMonth] = 0;
            }
            $userRegistrationData[$registrationMonth]++;

            // Calcul de l'espace total acheté et utilisé
            $totalStorage += $user->getTotalStorageSpace();
            $usedStorage += $user->getStorageUsed();
        }

        // Calculer l'espace libre
        $freeStorage = $totalStorage - $usedStorage;

        $totalFiles = $fileRepository->count([]);
        $totalFileSize = $fileRepository->createQueryBuilder('f')
            ->select('SUM(f.size) as totalSize')
            ->getQuery()
            ->getSingleScalarResult();

        // Répartition des types de fichiers
        $fileTypeDistribution = $fileRepository->createQueryBuilder('f')
            ->select('f.type, COUNT(f.id) as count')
            ->groupBy('f.type')
            ->getQuery()
            ->getResult();

        return $this->render('admin/dashboard.html.twig', [
            'dates' => $dates,
            'transactionsPerDate' => $transactionsPerDate,
            'amountsPerDate' => $amountsPerDate,
            'totalUsers' => $totalUsers,
            'verifiedUsers' => $verifiedUsers,
            'userRegistrationData' => $userRegistrationData,
            'totalStorage' => $totalStorage,
            'usedStorage' => $usedStorage,
            'freeStorage' => $freeStorage,
            'totalFiles' => $totalFiles,
            'totalFileSize' => $totalFileSize,
            'fileTypeDistribution' => $fileTypeDistribution,
        ]);
    }
}