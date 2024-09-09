<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ErrorController extends AbstractController
{
    #[Route('/404', name: 'app_404')]
    public function notFound(): Response
    {
        return $this->render('error404/error404.html.twig', [
            
        ]);
    }
}