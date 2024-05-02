<?php

namespace App\EventListener;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class SessionExpirationListener
{
    private $session;
    private $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // Vérifier si la session a expiré
        if (!$this->session->isStarted()) {
            // Récupérer les données du panier de l'utilisateur avant la fin de la session
            $cartData = $this->session->get('cart', []);

            // Restaurer les stocks des produits
            $this->restoreStocks($cartData);

            // Nettoyer le panier après la fin de la session
            $this->session->remove('cart');
        }
    }

    private function restoreStocks(array $cartData)
    {
        foreach ($cartData as $productId => $quantity) {
            // Récupérer le produit associé à l'ID
            $product = $this->entityManager->getRepository(Product::class)->find($productId);

            if ($product) {
                // Restaurer la quantité en stock
                $stock = $product->getStock();
                $stock->setQuantity($stock->getQuantity() + $quantity);

                // Mettre à jour la base de données
                $this->entityManager->flush();
            }
        }
    }
}
