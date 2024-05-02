<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart', name: 'app_cart_')]
class CartController extends AbstractController
{
    #[Route('/index', name : 'index')]
    public function indexCart(SessionInterface $session, ProductRepository $productRepository)
    {
        // $session->clear();
        $cart = $session->get('cart', []);
        $data = [];
        $total = 0;

        foreach ($cart as $id => $sizes) {
            foreach ($sizes as $size => $item) {
                // Accéder aux détails du produit à l'intérieur de chaque élément du panier
                $product = $item['product'];
                $quantity = $item['quantity'];
                $price = $item['price'];

                // Calculer le sous-total pour chaque produit et chaque taille
                $subtotal = $price * $quantity;

                // Ajouter le sous-total au total global
                $total += $subtotal;

                // Ajouter les détails du produit à $data pour l'affichage dans le template
                $data[] = [
                    'product' => $product,
                    'size' => $size,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }
        }
        return $this->render('cart/index.html.twig', compact('data', 'total'));
    }

    public function getCartTotal(SessionInterface $session, ProductRepository $productRepository)
{
    $cart = $session->get('cart', []);
    $total = 0;

    foreach($cart as $id => $sizes) {
        foreach ($sizes as $size => $item) {
            $product = $productRepository->find($id);
            $price = $item['price'];
            $quantity = $item['quantity'];
            $total += $price * $quantity;
        }
    }
    return $total;
}

public function getCartDescription(SessionInterface $session, ProductRepository $productRepository)
{
    $cart = $session->get('cart', []);
    $data = [];

    foreach($cart as $id => $sizes) {
        foreach ($sizes as $size => $item) {
            $product = $productRepository->find($id);
            $data[] = [
                'product' => $product,
                'size' => $size,
                'quantity' => $item['quantity'],
                'subtotal' => $item['price'] * $item['quantity'],
            ];
        }
    }
    return $data;
}


    // AJOUTER UN PRODUIT AU PANIER // 
    #[Route('/add/{id}', name: 'add')]
    public function addCart($id, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        // Récupérer le produit à partir de l'ID
        $product = $productRepository->find($id);
     
        // Récupérer la taille du produit à partir de la requête
        $size = $request->request->get('size');

        // Vérifier si le stock est disponible pour cette taille
        $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product, 'size' => $size]);

        if (!$stock || $stock->getQuantity() <= 0) {
            $this->addFlash('OutOfTheStock', "Désolé, votre produit n'a pas pu être ajouté au panier car ce produit n'est plus en stock");
            return $this->redirectToRoute('app_product');
        }
    
        // Récupérer le panier de la session
        $cart = $request->getSession()->get('cart', []);
    
        // Vérifier si le produit avec la même taille est déjà dans le panier
        if (isset($cart[$id][$size])) {
            // Si oui, vérifier si $cart[$id][$size] est un tableau
            if (is_array($cart[$id][$size])) {
                // Si c'est un tableau, augmenter la quantité
                $cart[$id][$size]['quantity']++;
            } else {
                // Sinon, initialiser $cart[$id][$size] comme un tableau avec les détails du produit
                $cart[$id][$size] = [
                    'product' => $product,
                    'size' => $size,
                    'quantity' => 1,
                    'price' => $product->getPrice(), // Ajoutez d'autres informations si nécessaire
                ];
            }
        } else {
            // Si le produit n'est pas déjà dans le panier, l'ajouter avec les détails
            $cart[$id][$size] = [
                'product' => $product,
                'size' => $size,
                'quantity' => 1,
                'price' => $product->getPrice(), // Ajoutez d'autres informations si nécessaire
            ];
        }
    
        // Décrémenter la quantité de stock
        $stock->setQuantity($stock->getQuantity() - 1);
        $entityManager->flush();

        // Mettre à jour la session du panier
        $request->getSession()->set('cart', $cart);
    
        // Rediriger vers la page de détails du produit
        return $this->redirectToRoute('app_cart_index');
    }


    // INCREMENTER 1 PRODUIT DANS LE PANIER 

    #[Route('/add-quantity/{id}/{size}', name: 'add_quantity', requirements: ['size' => '.*'])]
    public function addQuantity($id, $size, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        // Retrieve the product from the ID
        $product = $productRepository->find($id);

        // Retrieve the product size from the request
        $size = $size; // Assuming size is passed as a route parameter

        // Check if stock is available for this size
        $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product, 'size' => $size]);

        if (!$stock || $stock->getQuantity() <= 0) {
            $this->addFlash('OutOfTheStock', "Désolé, ce produit n'est plus en stock dans cette taille");
            return $this->redirectToRoute('app_product_details', ['id' => $id]);
        }

        // Retrieve the cart from the session
        $cart = $request->getSession()->get('cart', []);

        // Check if the product with the same size is already in the cart
        if (isset($cart[$id][$size])) {
            // If yes, check if $cart[$id][$size] is an array
            if (is_array($cart[$id][$size])) {
                // If it's an array, increment the quantity
                $cart[$id][$size]['quantity']++;
            } else {
                // If not, initialize $cart[$id][$size] as an array with product details
                $cart[$id][$size] = [
                    'product' => $product,
                    'size' => $size,
                    'quantity' => 1,
                    'price' => $product->getPrice(), // Add other information as needed
                ];
            }
        } else {
            // If the product is not already in the cart, add it with details
            $cart[$id][$size] = [
                'product' => $product,
                'size' => $size,
                'quantity' => 1,
                'price' => $product->getPrice(), // Add other information as needed
            ];
        }

        // Decrement stock quantity
        $stock->setQuantity($stock->getQuantity() - 1);
        $entityManager->flush();

        // Update the cart session
        $request->getSession()->set('cart', $cart);

        // Redirect to the product details page
        return $this->redirectToRoute('app_cart_index');
    }

    

    // RETIRER UN PRODUIT DU PANIER //
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Product $product, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $id = $product->getId();

        $cart = $session->get('cart', []);

        // Check if the product with the given ID exists in the cart
        if (!empty($cart[$id])) {
            // Loop through each size of the product in the cart
            foreach ($cart[$id] as $size => $item) {
                // Reduce the quantity of the product by 1
                if ($item['quantity'] > 0) {
                    $cart[$id][$size]['quantity']--;

                    // Update stock quantity
                    $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product, 'size' => $size]);
                    if ($stock) {
                        $stock->setQuantity($stock->getQuantity() + 1); // Increment stock by 1
                        $entityManager->flush();
                    }
                }

                // If quantity becomes 0, remove the product size from the cart
                if ($cart[$id][$size]['quantity'] <= 0) {
                    unset($cart[$id][$size]);
                }
            }
        }

        // Update the cart in the session
        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart_index');
    }


    // SUPPRIMER UN/DES PRODUITS DU PANIER //
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Product $product, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $id = $product->getId();

        $cart = $session->get('cart', []);

        // Parcourir chaque produit dans le panier
        foreach ($cart[$id] as $size => $item) {
            // Mettre à jour le stock pour chaque taille
            $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product, 'size' => $size]);
            if ($stock) {
                $stock->setQuantity($stock->getQuantity() + $item['quantity']);
                $entityManager->flush();
            }
        }

        // Supprimer le produit du panier
        unset($cart[$id][$size]);

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart_index');
    }


    // VIDER LE PANIER //
    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        // Récupérer les données du panier de l'utilisateur
        $cartData = $session->get('cart', []);

        // Parcourir chaque produit dans le panier
        foreach ($cartData as $productId => $sizes) {
            foreach ($sizes as $size => $item) {
                // Mettre à jour le stock pour chaque taille
                $product = $entityManager->getRepository(Product::class)->find($productId);
                $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product, 'size' => $size]);

                if ($stock) {
                    $stock->setQuantity($stock->getQuantity() + $item['quantity']);
                    $entityManager->flush();
                }
            }
        }

        // Vider le panier
        $session->remove('cart');

        return $this->redirectToRoute('app_cart_index');
    }
}