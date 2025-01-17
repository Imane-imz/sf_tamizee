<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {

        return $this->render('home/index.html.twig', [
            'products' => $productRepository->findBy([], ['id'=>'DESC']),
            'categories' => $categoryRepository->findAll()
        ]);
    }

    #[Route('/product/{id}/show', name: 'app_product_page_show', methods: ['GET'])]
    public function showProduct(Product $product, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $lastProducts = $productRepository->findBy([], ['id' => 'DESC'], 4);

        return $this->render('home/show.html.twig', [
            'product' => $product,
            'products' => $lastProducts,
            'categories' => $categoryRepository->findAll()
        ]);
    }

    #[Route('/product/category/{id}/filter', name: 'app_product_filter', methods: ['GET'])]
    public function filter($id, CategoryRepository $categoryRepository): Response
    {
        $products = $categoryRepository->find($id)->getProducts();
        $category = $categoryRepository->find($id);

        return $this->render('home/filter.html.twig', [
            'products' => $products,
            'category' => $category,
            'categories' => $categoryRepository->findAll()
        ]);
    }
}
