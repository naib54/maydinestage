<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;


class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(EntityManagerInterface $entityManager,): Response
    {
        $products = $entityManager->getRepository(Product::class)->findall();
        $category = $entityManager->getRepository(Category::class)->findall();


        if (!$products) {
            throw $this->createNotFoundException('No product found for id ');
        }
        
        return $this->render('product/index.html.twig', [
            'products' => $products, 'category' => $category,
        ]);
    }
}

