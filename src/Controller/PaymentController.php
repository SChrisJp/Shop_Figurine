<?php

namespace App\Controller;

use App\Entity\Order;
use App\Model\Cart;
use App\Repository\OrderRepository;
use App\Service\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends AbstractController
{
   

    /**
     * Méthode appelée lorsque le paiement est validé
     */
#[Route('/commande/valide', name: 'payment_success')]
public function paymentSuccess(OrderRepository $repository, EntityManagerInterface $em, Cart $cart)
{
    // Récupération de la dernière commande de l'utilisateur
    $order = $repository->findOneBy(['user' => $this->getUser()], ['createdAt' => 'DESC']);

    // Vérification de la commande
    if (!$order) {
        throw $this->createNotFoundException('Commande introuvable');
    }

    // Mise à jour de l'état de la commande
    if (!$order->getState()) {
        $order->setState(1);
        $em->persist($order);
        $em->flush();
    }

    // Réinitialisation du panier
    $cart->clear();

    return $this->redirectToRoute('order_confirmation', ['reference' => $order->getReference()]);
}





    /**
     * Commande annullée (clic sur retour dans la fenêtre)
     */
  #[Route('/commande/echec/{stripeSession}', name: 'payment_fail')]
public function paymentFail(OrderRepository $repository, $stripeSession) 
{
    $order = $repository->findOneByStripeSession($stripeSession);
    if (!$order || $order->getUser() != $this->getUser()) {
        throw $this->createNotFoundException('Commande inaccessible');
    }

    return $this->redirectToRoute('order_confirmation', ['reference' => $order->getReference()]);
}

#[Route('/commande/confirmation/{reference}', name: 'order_confirmation')]
public function orderConfirmation(OrderRepository $repository, $reference) 
{
    $order = $repository->findOneByReference($reference);
    if (!$order) {
        throw $this->createNotFoundException('Cette commande n\'existe pas');
    }

    return $this->render('payment/success.html.twig', [
        'order' => $order
    ]);
}

}
