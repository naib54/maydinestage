<?php

namespace App\Controller;

use App\Entity\Product;


use App\Entity\Stock;
use App\Form\ProductFilterType;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product', methods: ['GET', 'POST'])]
    public function productList(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, PaginatorInterface $paginatorInterface): Response
    {
        // Récupérer toutes les catégories
        $categories = $categoryRepository->findAll();

        // Récupérer tous les produits sans pagination
        $productsQuery = $entityManager->getRepository(Product::class)->findAll();

        // Paginer les résultats de la requête des produits
        $paginator = $paginatorInterface->paginate(
            $productsQuery, // Requête de tous les produits
            $request->query->getInt('page', 1), // Numéro de page à afficher, par défaut 1
            6 // Nombre d'éléments par page
        );

        // Créer un formulaire de filtre pour les catégories de produits
        $form = $this->createForm(ProductFilterType::class);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la catégorie sélectionnée dans le formulaire
            $selectedCategory = $form->get('category')->getData();
            
            // Récupérer la sous-catégorie sélectionnée dans le formulaire
            $selectedSubCategory = $form->get('subcategory')->getData();

            // Filtrer les produits en fonction de la catégorie et de la sous-catégorie sélectionnées
            $filteredProducts = $entityManager->getRepository(Product::class)->findByCategoryAndSubcategory($selectedCategory, $selectedSubCategory);

            // Paginer les résultats filtrés
            $filteredPaginator = $paginatorInterface->paginate(
                $filteredProducts, // Résultats filtrés
                $request->query->getInt('page', 1), // Numéro de page à afficher, par défaut 1
                6 // Nombre d'éléments par page
            );

            // Assigner les résultats filtrés paginés à la variable de pagination
            $paginator = $filteredPaginator;
        }

        // Rendre la vue Twig avec les données nécessaires
        return $this->render('product/list.html.twig', [
            'categories' => $categories,
            'products' => $paginator, // Assigner la variable de pagination à la vue
            'cat_subc_form' => $form->createView(), // Formulaire de filtre des catégories
        ]);
    }

    #[Route('/product/load-subcategories', name: 'load_subcategories', methods: ['GET'])]
    public function loadSubcategories(Request $request, SubCategoryRepository $subCategoryRepository): JsonResponse
    {
        $categoryId = $request->query->get('category_id');
        $subcategories = $subCategoryRepository->findBy(['category' => $categoryId]);

        $subcategoriesArray = [];
        foreach ($subcategories as $subcategory) {
            $subcategoriesArray[$subcategory->getId()] = $subcategory->getName();
        }

        return new JsonResponse(['subcategories' => $subcategoriesArray]);
    }

    #[Route('/products/details/{id}', name: 'app_product_details')]
    public function produtDetails($id, EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->find($id);
        $stocks = $entityManager->getRepository(Stock::class)->findBy(['product' => $products]);

        return $this->render('product/details.html.twig', [
            'products' => $products,
            'stocks' => $stocks

        ]);
    }
}

