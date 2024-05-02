<?php

// src/Controller/LocaleController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LocaleController extends AbstractController
{
    #[Route('/change-locale', name: 'change_locale', methods: ['GET', 'POST'])]
    public function changeLocale(Request $request): Response
    {
        $locale = $request->request->get('locale'); // Récupérez la locale depuis le formulaire

        // Si la locale n'est pas dans le formulaire POST, essayez de la récupérer depuis les paramètres GET
        if (!$locale) {
            $locale = $request->query->get('locale');
        }

        if ($locale) {
            // Stockez la locale dans la session de l'utilisateur
            $request->getSession()->set('_locale', $locale);
        }

        // Redirigez l'utilisateur vers la page précédente ou une autre page
        return $this->redirectToRoute('home');
    }
}
