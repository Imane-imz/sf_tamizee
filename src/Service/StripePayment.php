<?php

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePayment
{
    private $redirectUrl;

    public function __construct()
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);
        Stripe::setApiVersion('2025-01-27.acacia');
    }

    public function startPayment($cart, $shippingFees, $orderId)
    {
        $cartProducts = $cart['cart'];
        $products = [
            [
                'quantity' => 1,
                'price' => $shippingFees,
                'name' => "Frais de livraison"
            ]
        ];

       /*  if ($shippingCost > 0) {
            $products[] = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Frais de livraison'
                    ],
                    'unit_amount' => (int) ($shippingCost * 100) // Convertir les centimes
                ],
            ];
        } */

        foreach ($cartProducts as $value) {
            $productItem = [];
            $productItem['name'] = $value['product']->getName();
            $productItem['price'] = $value['product']->getPrice();
            $productItem['quantity'] = $value['quantity'];
            $products[] = $productItem; 
        } 

        $session = Session::create([
            'line_items' => [
                array_map(fn(array $product)=>[
                    'quantity' => $product['quantity'],
                    'price_data' => [
                        'currency' => 'EUR',
                        'product_data' => [
                            'name' => $product['name']
                        ],
                        'unit_amount' => $product['price']*100
                    ],
                ], $products)
            ],
            'mode' => 'payment',
            'cancel_url' => 'https://127.0.0.1:8000/payment/cancellation',
            'success_url' => 'https://127.0.0.1:8000/payment/success',
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ["US", "CA", "GB", "FR", "DE", "IT", "ES", "AU", "BR", "IN", "JP", "CN", "MX", "NL", "SE", 
                                        "CH", "BE", "AT", "DK", "FI", "NO", "IE", "PL", "PT", "RU", "SG", "HK", "KR", "NZ", "ZA"]
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'orderId' => $orderId
                ] 
            ]
            
        ]);

        $this->redirectUrl = $session->url;
    }

    public function getStripeRedirectUrl()
    {
        return $this->redirectUrl;
    }
}