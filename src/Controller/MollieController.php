<?php 

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
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
    public function index(CartController $cartController, ProductRepository $productRepository, SessionInterface $session): Response
    {
        // Calcul du total du panier
        $total = $cartController->getCartTotal($session, $productRepository);
        
        // Initialisation de Mollie avec votre clé d'API
        $mollie = new MollieApiClient();
        $mollie->setApiKey($_ENV["MOLLIE_API_KEY"]);

        // Création du paiement avec Mollie
        $payment = $mollie->payments->create([
            "amount" => [
                "currency" => "EUR",
                "value" => number_format($total, 2, '.', ''), // Le montant doit être en format décimal avec deux décimales
            ],
            "description" => "Achat sur votre site",
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

    #[Route('/mollie/cancel', name: 'app_mollie_cancel')]
    public function mollieCancel(Request $request, SessionInterface $session)
    {
        $this->addFlash(
            'alert',
            'Paiement refusé! Blablabla.'
        );

        return $this->redirectToRoute('app_home');
    }

}