<?php
// src/Controller/StorageController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StorageController extends AbstractController
{
    #[Route('/plans', name: 'app_plans')]
    #[IsGranted('ROLE_USER')]  // Seuls les utilisateurs connectés peuvent accéder
    public function index(): Response
    {
        // Définir les plans
        $plans = [
            [
                'name' => 'Plan de Base',
                'price' => 5.00,
                'storage' => '10 Go',
                'description' => 'Ajoutez 10 Go d\'espace de stockage pour gérer vos fichiers.'
            ],
            [
                'name' => 'Plan Standard',
                'price' => 10.00,
                'storage' => '50 Go',
                'description' => 'Ajoutez 50 Go d\'espace de stockage pour vos projets plus importants.'
            ],
            [
                'name' => 'Plan Premium',
                'price' => 20.00,
                'storage' => '200 Go',
                'description' => 'Profitez de 200 Go de stockage pour tous vos besoins d\'entreprise.'
            ],
        ];

        return $this->render('storage/plans.html.twig', [
            'plans' => $plans,
        ]);
    }
}
