<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository)
    {

    }

    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function index(SessionInterface $session, Cart $cart): Response
    {
        $data = $cart->getCart($session);

        $cartProducts = $data['cart'];

        $product = [];

        foreach ($cartProducts as $value){
            
        }

        return $this->render('cart/index.html.twig', [
            'items' => $data['cart'],
            'total' => $data['total']
        ]);
    }

    #[Route('/cart/add/{id}/', name: 'app_cart_new', methods: ['GET'])]
    public function addToCart(int $id, SessionInterface $session, Request $request, Product $product): Response
    {
        $cart = $session->get('cart', []);
        $quantity = $request->query->getInt('quantity', 1); // ← ici la quantité est lue depuis l'URL

        if (isset($cart[$product->getId()])) {
            $cart[$product->getId()] += $quantity;
        } else {
            $cart[$product->getId()] = $quantity;
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Produit ajouté au panier');


        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}/', name: 'app_cart_product_remove', methods: ['GET'])]
    public function removeFromCart($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])){
            unset($cart[$id]);
        }
        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove', name: 'app_cart_remove', methods: ['GET'])]
    public function deleteCart(SessionInterface $session): Response
    {
        $session->set('cart', []);

        return $this->redirectToRoute('app_cart');
    }
}
