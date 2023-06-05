<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivateController extends AbstractController
{
    #[Route('/activate', name: 'app_activate')]
    public function index(): JsonResponse
    {
        
    }
}
