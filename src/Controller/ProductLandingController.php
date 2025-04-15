<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductLandingController extends AbstractController
{
    #[Route('/all/products', name: 'app_all_products', methods: ['GET'])]
    public function index(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $productRepository->findAll(), // Tu peux utiliser une requête DQL ou QueryBuilder si tu veux paginer proprement
            $request->query->getInt('page', 1),
            12 // nombre de produits par page
        );
    
        return $this->render('product_landing/index.html.twig', [
            'products' => $pagination
        ]);
    }
}
