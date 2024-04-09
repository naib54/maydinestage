<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\ProductFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;


class ProductController extends AbstractController
{
    // #[Route('/product', name: 'app_product')]
    // public function productList(EntityManagerInterface $entityManager)
    // {
    //     $products = $entityManager->getRepository(Product::class)->findAll();

    //     return $this->render('product/list.html.twig', [
    //         'products' => $products,
    //     ]);
    // }

    #[Route('/product', name: 'app_product', methods: ['GET', 'POST'])]
    public function productList(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();

        // Récupérer tous les produits par défaut
        $products = $entityManager->getRepository(Product::class)->findAll();

        $form = $this->createForm(ProductFilterType::class);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $category = $data['category'];
            $subcategory = $data['subcategory'];

            // Filtrer les produits en fonction de la catégorie et de la sous-catégorie sélectionnées
            $products = $entityManager->getRepository(Product::class)->findByCategoryAndSubcategory($category, $subcategory);
        }

        return $this->render('product/list.html.twig', [
            'categories' => $categories,
            'products' => $products,
            'form' => $form->createView(),
        ]);
    }
}
