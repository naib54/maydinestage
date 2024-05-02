<?php 

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Mollie\Api\MollieApiClient;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\CartController;


class MollieController extends AbstractController
{
    #[Route('/mollie', name: 'app_mollie')]
    public function index(CartController $cartController, ProductRepository $productRepository, OrderRepository $orderRepository, SessionInterface $session): Response
    {
        // Calcul du total du panier
        $total = $cartController->getCartTotal($session, $productRepository);
        // dd($total);
        // Vérifiez si le panier est vide
        if ($total <= 0) {
            $this->addFlash('EmptyCart', 'Votre panier est vide');
            return $this->redirectToRoute('app_product');
        }

        // Initialisation de Mollie avec votre clé d'API
        $mollie = new MollieApiClient();
        $mollie->setApiKey($_ENV["MOLLIE_API_KEY"]);

        // Récupérer l'utilisateur actuel
        $user = $this->getUser();

        // Récupérer la dernière commande de l'utilisateur
        $lastOrder = $orderRepository->findLastOrderByUser($user);

        // Construction de la description du paiement
        $description = "Commande n°" . $lastOrder->getId() . " - " . $lastOrder->getReference();


        // Création du paiement avec Mollie
        $payment = $mollie->payments->create([
            "amount" => [
                "currency" => "EUR",
                "value" => number_format($total, 2, '.', ''), // Le montant doit être en format décimal avec deux décimales
            ],
            "description" => $description,
            "redirectUrl" => $this->generateUrl('app_mollie_confirm', [], UrlGeneratorInterface::ABSOLUTE_URL),
            "cancelUrl" => $this->generateUrl('app_mollie_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            // Ajoutez d'autres paramètres Mollie selon vos besoins
        ]);

        // Stockez l'ID de paiement dans la session pour récupération lors de la confirmation
        $session->set('payment_id', $payment->id);

        // Redirection vers la page de paiement Mollie
        return $this->redirect($payment->getCheckoutUrl(), 303);
    }

    #[Route('/mollie/confirm', name: 'app_mollie_confirm')]
    public function mollieConfirm(Request $request, SessionInterface $session)
    {
        // Vérifiez ici l'état du paiement et confirmez la commande si nécessaire
        // Mettez à jour l'état de la commande dans votre système

        $this->addFlash(
            'success',
            'Paiement réussi! Votre commande est en cours de préparation et un email de confirmation vous sera envoyé prochainement.'
        );

        // Videz le panier
        $session->remove('cart');

        // Redirigez vers la page d'accueil ou toute autre page appropriée
        return $this->redirectToRoute('app_home');
    }

    // #[Route('/mollie/cancel', name: 'app_mollie_cancel')]
    // public function mollieCancel(Request $request, SessionInterface $session)
    // {
    //     $this->addFlash(
    //         'alert',
    //         'Paiement refusé! Blablabla.'
    //     );

    //     return $this->redirectToRoute('app_home');
    // }

    #[Route('/mollie/cancel', name: 'app_mollie_cancel')]
    public function mollieCancel(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, OrderRepository $orderRepository)
    {
        // Récupérer l'utilisateur actuel
        $user = $this->getUser();

        // Récupérer la dernière commande de l'utilisateur
        $lastOrder = $orderRepository->findLastOrderByUser($user);

        // Vérifier si une dernière commande existe
        if ($lastOrder !== null) {
            // Supprimer les détails de commande associés à cette commande
            foreach ($lastOrder->getOrderDetails() as $orderDetail) {
                $entityManager->remove($orderDetail);
            }

            // Supprimer la commande elle-même
            $entityManager->remove($lastOrder);
            $entityManager->flush();
        }

        $this->addFlash(
            'alert',
            'Le paiement a été annulé. Votre commande n\'a pas été validée.'
        );

        return $this->redirectToRoute('app_home');
    }

}
