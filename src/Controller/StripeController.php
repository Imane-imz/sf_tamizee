<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Service\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/payment/success', name: 'app_stripe_success')]
    public function success(Cart $cart, SessionInterface $session): Response
    {
        $session->set('cart', []);
        
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/payment/cancellation', name: 'app_stripe_cancellation')]
    public function cancellation(): Response
    {
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/stripe/notification', name: 'app_stripe_notification', methods: ['POST'])]
    public function stripeNotification(Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);

        $endpoint_secret = 'whsec_e4eb3b3ad9125ff208d8b90481809d515fdd0832f02f7e0381bee9464d71ffad';

        $payload = $request->getContent();

        $sig_header = $request->headers->get('Stripe-Signature');

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, // Corps du webhook
                $sig_header, // En-tête de la signature
                $endpoint_secret // Clé secrète
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('Erreur lors de la réception du webhook', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Signature invalide');
        }

        switch ($event->type){
            case 'payment_intent.succeeded': //Dans le cas où ça contient l'objet payment_intent
                $paymentIntent = $event->data->object;

                $filename = __DIR__ . '/public/stripe-details-' . uniqid() . '.txt';

                $orderId = $paymentIntent->metadata->orderId;

                $order = $orderRepository->find($orderId);

                $cartPrice = $order->getTotalPrice();

                $stripeTotalAmount = $paymentIntent->amount/100;

                if ($cartPrice==$stripeTotalAmount){
                    $order->setPaymentCompleted(1);
                    $entityManager->persist($order);
                    $entityManager->flush();
                }

                // file_put_contents($filename, json_encode($orderId));
                break;
            case 'payment_method.attached': //Dans le cas où ça contient l'objet payment_method
                $paymentMethod = $event->data->object;
                break;
            default :
                file_put_contents(__DIR__ . '/public/unknown-event.txt', json_encode($event));
                break;
        }

        return new Response('Evénement reçu: ' . $event->type, 200);
    }

    #[Route('/checkout/{id}', name: 'app_checkout', methods: ['POST'])]
    public function checkout(Product $product, Request $request): Response
    {
        \Stripe\Stripe::setApiKey($_SERVER['STRIPE_SECRET']);

        $quantity = $request->request->get('quantity', 1);

        // Sécurité minimale
        if ($quantity < 1) {
            $quantity = 1;
        }

        $checkoutSession = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => $product->getPrice() * 100,
                ],
                'quantity' => $quantity,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_stripe_cancellation', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkoutSession->url);
    }
}
