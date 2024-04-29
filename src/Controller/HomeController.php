<?php

namespace App\Controller;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\LocaleSwitcher;



class HomeController extends AbstractController
{

    
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findall();
        $category = $entityManager->getRepository(Category::class)->findall();


        if (!$products) {
            throw $this->createNotFoundException('No product found for id ');
        }
        if (!$category) {
            throw $this->createNotFoundException('No product found for category ');
        }

        return $this->render('home/index.html.twig', [
            'products' => $products, 'category' => $category,
        ]);
    }
        
    

}
