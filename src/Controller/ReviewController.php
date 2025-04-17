<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/product/{productId}/review/new', name: 'review_new')]
    public function new(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager, Security $security, int $productId): Response
    {
        $product = $productRepository->find($productId);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $review = new Review();
        $review->setCreatedAt(new \DateTimeImmutable());
        $review->setUsername($security->getUser()->getUserIdentifier());

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($review->getRating() === null) {
                $this->addFlash('danger', 'Merci de sélectionner une note.');
            } else {
                $review->setProduct($product);
                $review->setUsername($this->getUser()->getUserIdentifier());
                $entityManager->persist($review);
                $entityManager->flush();
        
                return $this->redirectToRoute('app_product_page_show', ['id' => $product->getId()]);
            }
        }        

        return $this->render('review/new.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }
}
