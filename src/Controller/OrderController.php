<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order', name: 'app_order_')]
class OrderController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $entityManagerInterface): Response
    {
        $cart = $session->get('cart', []);
        
        if($cart === []) {
            $this->addFlash('EmptyCart', 'Votre panier est vide');
            return $this->redirectToRoute('app_product');
        }

        // Le panier n'est pas vide, on crée la commande
        $order = new Order();

        // On remplit la commande
        // $order->setUser($this->getUser());
        $order->setReference(uniqid());

        // on parcourt le panier pour créer les details de commande
        foreach($cart as $item => $quantity) {
            $orderDetail = new OrderDetail();

            // on va chercher le produit
            $product = $productRepository->find($item);
            $price = $product->getPrice();

            // on crée le detail de commande
            $orderDetail->setProducts($product);
            $orderDetail->setPrice($price);
            $orderDetail->setQuantity($quantity);

            $order->addOrderDetail($orderDetail);
        }

        // On persiste et on flush (créer les requetes et exécute)
        $entityManagerInterface->persist($order);
        $entityManagerInterface->flush();

        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }
}
