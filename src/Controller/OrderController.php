<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderedProducts;
use App\Entity\User;
use App\Form\OrderFormType;
use App\Repository\OrderRepository;
use App\Service\Cart;
use App\Service\StripePayment;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    public function __construct(private MailerInterface $mailer){}

    #[Route('/order', name: 'app_order')]
    public function index(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, Cart $cart, Security $security): Response
    {
        $data = $cart->getCart($session);

        $order = new Order();

        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($data['total'])){
                $order->setTotalPrice($data['total']);
                $order->setCreatedAt(new \DateTimeImmutable());
                $order->setPaymentCompleted(0);

                // Récupérer l'utilisateur connecté
                $user = $security->getUser();
                if (!$user instanceof User) {
                    throw new \Exception("Utilisateur non reconnu.");
                }

                // Associer l'email de l'utilisateur à la commande
                $order->setEmail($user->getEmail());

                $entityManager->persist($order);
                $entityManager->flush();

                foreach ($data['cart'] as $value){
                    $orderedProducts = new OrderedProducts();
                    $orderedProducts->setOrder($order);
                    $orderedProducts->setProduct($value['product']);
                    $orderedProducts->setQuantity($value['quantity']);

                    $entityManager->persist($orderedProducts);
                    $entityManager->flush();
                }

                // Envoi de l'email de confirmation
                $html = $this->renderView('emails/orderConfirmation.html.twig', [
                    'order' => $order
                ]);

                $email = (new Email())
                    ->from('contact@tamizee.com')
                    ->to($user->getEmail())
                    ->subject('Confirmation de votre commande')
                    ->html($html);

                $this->mailer->send($email);

                // Gestion du paiement Stripe
                $payment = new StripePayment();

                $city = $order->getCity();
                $shippingFees = $city ? $city->getShippingFees() : 0; 

                $payment->startPayment($data, $shippingFees, $order->getId());
                $stripeRedirectUrl = $payment->getStripeRedirectUrl();

                return $this->redirect($stripeRedirectUrl);
            }

            return $this->redirectToRoute('app_order_success');
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' =>$data['total'],
            'items' => $data['cart']
        ]);        
    }

    #[Route('/order-success', name: 'app_order_success')]
    public function afterOrder(): Response
    {
        return $this->render('order/order_success.html.twig');
    }

    #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    { 
        $cityShippingPrice = $city->getShippingFees();

        return new Response(json_encode(['status' => 200, 'message' => 'ok', 'content' => $cityShippingPrice]));
    }

    #[Route('/admin/order/{type}/', name: 'app_order_show')]
    public function getAllOrders($type, OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request): Response
    {
        if ($type == 'is-delivered') {
            $data = $orderRepository->findBy(['isDelivered'=>1], ['createdAt'=>'DESC']);
        } elseif ($type == 'is-not-delivered') {
            $data = $orderRepository->findBy(['isDelivered'=>null], ['createdAt'=>'DESC']);
        } elseif ($type == 'all-orders') {
            $data = $orderRepository->findBy([], ['createdAt'=>'DESC']);
        }
        
        $order = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            10
        );
        

        return $this->render('order/orders.html.twig', [
            'orders' => $order,
        ]);
    }

    #[Route('/admin/order/{id}/is-delivered/update', name: 'app_order_is_delivered_update')]
    public function isDeliveredUpdate($id, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response 
    {
        $order = $orderRepository->find($id);
        $order->setDelivered(true);

        $entityManager->flush();

        $this->addFlash('success', 'La commande a été livrée.');

        return $this->redirectToRoute('app_order_show', ['type'=>'all-orders']);
    }
}
