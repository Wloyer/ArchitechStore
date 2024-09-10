<?php

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
        // Plans mis à jour avec le tarif de 20€ pour 20 Go
        $plans = [
            [
                'name' => '20 Go supplémentaires',
                'price' => 20.00,
                'storage' => '20 Go',
                'description' => 'Ajoutez 20 Go d\'espace de stockage supplémentaire.'
            ],
            [
                'name' => '40 Go supplémentaires',
                'price' => 40.00,
                'storage' => '40 Go',
                'description' => 'Ajoutez 40 Go d\'espace de stockage supplémentaire.'
            ],
            [
                'name' => '60 Go supplémentaires',
                'price' => 60.00,
                'storage' => '60 Go',
                'description' => 'Ajoutez 60 Go d\'espace de stockage supplémentaire.'
            ],
        ];

        return $this->render('storage/plans.html.twig', [
            'plans' => $plans,
        ]);
    }
}
