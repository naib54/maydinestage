<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
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
        $cart = $session->get('cart', []);
        
        $data = [];
        $total = 0;

        foreach($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            $data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $total += $product->getPrice() * $quantity;
        }
        return $this->render('cart/index.html.twig', compact('data', 'total'));
    }

    public function getCartTotal(SessionInterface $session, ProductRepository $productRepository)
    {
        $cart = $session->get('cart', []);
        
        $data = [];
        $total = 0;

        foreach($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            $data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $total += $product->getPrice() * $quantity;
        }
        return $total;
    }

    public function getCartDescription(SessionInterface $session, ProductRepository $productRepository)
    {
        $cart = $session->get('cart', []);
        
        $data = [];
        $total = 0;

        foreach($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            $data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $total += $product->getPrice() * $quantity;
        }
        return $data;
    }

    // #[Route('/add/{id}', name: 'add')]
    // public function addCart(Product $product, SessionInterface $session)
    // {
    //     $id = $product->getId();
    //     $cart = $session->get('cart', []);

    //     if (empty($cart[$id])) {
    //         $cart[$id] = 1;
    //     } else {
    //         $cart[$id]++;
    //     }

    //     $session->set('cart', $cart);

    //     return $this->redirectToRoute('app_cart_index');
    // }

    #[Route('/add/{id}', name: 'add')]
    public function addCart($id, Product $product, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product]);

    if (!$stock || $stock->getQuantity() <= 0) {
        $this->addFlash('OutOfTheStock', "Désolé, votre produit n'as pas pu être ajouter au panier car ce produit n'est plus en stock");
        return $this->redirectToRoute('app_product');
    }

    $id = $product->getId();
    $cart = $session->get('cart', []);

    if (empty($cart[$id])) {
        $cart[$id] = 1;
    } else {
        $cart[$id]++;
    }

    $stock->setQuantity($stock->getQuantity() - 1);
    $entityManager->flush();

    $session->set('cart', $cart);

    return $this->redirectToRoute('app_cart_index');
    }

    // #[Route('/remove/{id}', name: 'remove')]
    // public function remove(Product $product, SessionInterface $session)
    // {
    //     $id = $product->getId();

    //     $cart = $session->get('cart', []);

    //     if(!empty($cart[$id])){
    //         if($cart[$id] > 1){
    //             $cart[$id]--;
    //         }else{
    //             unset($cart[$id]);
    //         }
    //     }

    //     $session->set('cart', $cart);
        
    //     return $this->redirectToRoute('app_cart_index');
    // }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Product $product, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $id = $product->getId();

    $cart = $session->get('cart', []);

    if (!empty($cart[$id])) {
        $quantityToRemove = 1; // Assuming you're removing one product at a time

        if ($quantityToRemove > $cart[$id]) {
            // This should never happen, but just in case
            $quantityToRemove = $cart[$id];
        }

        $cart[$id] -= $quantityToRemove;

        // Update stock quantity
        $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product]);
        if ($stock) {
            $stock->setQuantity($stock->getQuantity() + $quantityToRemove);
            $entityManager->flush();
        }
    }

    $session->set('cart', $cart);

    return $this->redirectToRoute('app_cart_index');
    }

    // #[Route('/delete/{id}', name: 'delete')]
    // public function delete(Product $product, SessionInterface $session)
    // {
    //     $id = $product->getId();

    //     $cart = $session->get('cart', []);

    //     if(!empty($cart[$id])){
    //         unset($cart[$id]);
    //     }

    //     $session->set('cart', $cart);

    //     return $this->redirectToRoute('app_cart_index');
    // }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Product $product, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $id = $product->getId();

        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $quantityToRemove = $cart[$id]; // Assuming all products are removed at once

            unset($cart[$id]);

            // Update stock quantity
            $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product]);
            if ($stock) {
                $stock->setQuantity($stock->getQuantity() + $quantityToRemove);
                $entityManager->flush();
            }
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart_index');
    }

    private function restoreProductsFromCart(array $cartData, EntityManagerInterface $entityManager)
    {
        foreach ($cartData as $productId => $quantity) {
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $product->setQuantity($product->getQuantity() + $quantity);
                $entityManager->flush();
            }
        }
    }

    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $session->remove('cart');

        // Restore products from cart
        $cartData = $session->get('cart', []);
        $this->restoreProductsFromCart($cartData, $entityManager);

        return $this->redirectToRoute('app_cart_index');
    }

    // #[Route('/empty', name: 'empty')]
    // public function empty(SessionInterface $session)
    // {
    //     $session->remove('cart');

    //     return $this->redirectToRoute('app_cart_index');
    // }
}