<?php

namespace App\Controller;

use App\Entity\Stock;
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
    public function add(SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $cart = $session->get('cart', []);
        
        if(empty($cart)) {
            $this->addFlash('EmptyCart', 'Votre panier est vide');
            return $this->redirectToRoute('app_product');
        }

        // Le panier n'est pas vide, on crée la commande
        $order = new Order();

        // On remplit la commande
        $order->setUser($this->getUser());
        $order->setReference(uniqid());

        // on parcourt le panier pour créer les détails de commande
        foreach($cart as $productId => $sizes) {
            foreach ($sizes as $size => $quantity) {
                $orderDetail = new OrderDetail();

                // on va chercher le produit
                $product = $productRepository->find($productId);
                $price = $product->getPrice();

                // on crée le détail de commande
                $orderDetail->setProducts($product);
                $orderDetail->setPrice($price);
                $orderDetail->setQuantity((int)$quantity); // Assurez-vous que $quantity est un entier

                // Si la taille est NULL ou vide, ne pas associer de stock
                if (!empty($size)) {
                    // Récupérer le stock correspondant à ce produit et à cette taille
                    $stock = $entityManager->getRepository(Stock::class)->findOneBy(['product' => $product, 'size' => $size]);

                    // Assurez-vous que le stock est trouvé avant d'associer à l'OrderDetail
                    if ($stock) {
                        $orderDetail->setStock($stock);
                    } else {
                        // Gérer le cas où le stock n'est pas trouvé
                        // Vous pouvez générer un message d'erreur ou gérer de toute autre manière appropriée
                    }
                }

                $order->addOrderDetail($orderDetail);
            }
        }

        // On persiste et on flush (créer les requêtes et exécute)
        $entityManager->persist($order);
        $entityManager->flush();

        // Vider le panier après avoir passé la commande
        // $session->set('cart', []);

        return $this->redirectToRoute('app_mollie');
    }
}
