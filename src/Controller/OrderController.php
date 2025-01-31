<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderedProducts;
use App\Form\OrderFormType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(Request $request, SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $entityManager, Cart $cart): Response
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

            $session->set('cart', []);
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
}
