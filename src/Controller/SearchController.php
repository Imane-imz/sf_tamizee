<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET', 'POST'])]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        if ($request->isMethod('GET', 'POST')) {
            $data = $request->query->all();
            $word = $data['word'];
            $results = $productRepository->searchEngine($word);
        }

        return $this->render('search/index.html.twig', [
            'products' => $results,
            'word' => $word
        ]);
    }
}
