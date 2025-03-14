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
    public function index(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, Cart $cart, OrderRepository $orderRepository): Response
    {
        $data = $cart->getCart($session);

        $order = new Order();

        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($data['total'])){
                $order->setTotalPrice($data['total']);
                $order->setCreatedAt(new \DateTimeImmutable());

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
            }

            // Vider panier après validation
            $session->set('cart', []);

            // Récupérer l'utilisateur connecté + envoi de l'email
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new \Exception("Utilisateur non reconnu.");
            }

            $html = $this->renderView('emails/orderConfirmation.html.twig', [
                'order' => $order
            ]);

            $email = (new Email())
                ->from('contact@tamizee.com')
                ->to($user->getEmail())
                ->subject('Confirmation de votre commande')
                ->html($html);

            $this->mailer->send($email);

            $payment = new StripePayment();

            // Vérifier si la ville est bien définie avant de récupérer les frais de livraison
            $city = $order->getCity();
            $shippingFees = $city ? $city->getShippingFees() : 0; // Utiliser 0 si la ville est null

            // Lancer le paiement avec les frais de livraison
            $payment->startPayment($data, $shippingFees);

            $stripeRedirectUrl = $payment->getStripeRedirectUrl();

            return $this->redirect($stripeRedirectUrl);

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

    #[Route('/admin/order', name: 'app_order_show')]
    public function getAllOrders(OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $data = $orderRepository->findBy([], ['createdAt'=>'DESC']);
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

        return $this->redirectToRoute('app_order_show');
    }
}
