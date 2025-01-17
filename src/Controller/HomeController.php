<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $data = $productRepository->findBy([], ['id'=>'DESC']);
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            2
        );

        return $this->render('home/index.html.twig', [
            'products' => $products,
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
    public function filter($id, CategoryRepository $categoryRepository, ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $data = $productRepository->findBy([], ['id'=>'DESC']);
        $productsByCategory = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            4
        );
        
        $products = $categoryRepository->find($id)->getProducts();
        $category = $categoryRepository->find($id);

        return $this->render('home/filter.html.twig', [
            'productsbyCategory' => $productsByCategory,
            'products' => $products,
            'category' => $category,
            'categories' => $categoryRepository->findAll()
        ]);
    }
}
