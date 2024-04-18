<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderdetailsController extends AbstractController
{
    #[Route('/orderdetails', name: 'app_orderdetails')]
    public function index(): Response
    {
        return $this->render('orderdetails/index.html.twig', [
            'controller_name' => 'OrderdetailsController',
        ]);
    }
}
