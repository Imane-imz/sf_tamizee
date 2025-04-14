<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\AddProductHistoryType;
use App\Form\ProductEditType;
use App\Form\ProductType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $data = $productRepository->findBy([], ['id'=>'DESC']);
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
            /* 'categories' => $categories */
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 📌 Image principale
            $mainImage = $form->get('image')->getData();
            if ($mainImage) {
                $originalName = pathinfo($mainImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName.'-'.uniqid().'.'.$mainImage->guessExtension();
    
                try {
                    $mainImage->move(
                        $this->getParameter('image_dir'),
                        $newFileName
                    );
                } catch(FileException $exception) {
                    // log error
                }
    
                $product->setImage($newFileName); // image principale
            }
    
            // 📸 Galerie d’images
            $galleryImages = $form->get('images')->getData();
    
            foreach ($galleryImages as $imageFile) {
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('image_dir'),
                        $newFileName
                    );
                } catch(FileException $exception) {
                    // log error
                }
    
                $productImage = new ProductImage();
                $productImage->setImagePath($newFileName);
                $productImage->setProduct($product);
    
                $entityManager->persist($productImage);
            }
    
            // 🔐 Enregistrer le produit
            $entityManager->persist($product);
            $entityManager->flush();
    
            // 🕓 Historique
            $stockHistory = new AddProductHistory();
            $stockHistory->setQuantity($product->getStock());
            $stockHistory->setProduct($product);
            $stockHistory->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($stockHistory);
            $entityManager->flush();
    
            $this->addFlash('success', 'Le produit a bien été ajouté.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductEditType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Image principale
            $image = $form->get('image')->getData();
            if ($image) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName.'-'.uniqid().'.'.$image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('image_dir'),
                        $newFileName
                    );
                } catch(FileException $exception) {
                    // Tu peux logguer l'erreur si besoin
                }

                $product->setImage($newFileName);
            }

            // Images supplémentaires
            foreach ($form->get('productImages') as $key => $productImageForm) {
                $imageFile = $productImageForm->get('imageFile')->getData();
                $delete = $productImageForm->get('delete')->getData();
                $productImageEntity = $product->getProductImages()[$key] ?? null;
            
                if ($imageFile && $productImageEntity) {
                    // Supprimer l'ancienne image physique si tu veux
                    $oldImagePath = $productImageEntity->getImagePath();
                    if ($oldImagePath) {
                        @unlink($this->getParameter('image_dir') . '/' . $oldImagePath);
                    }
            
                    // Upload de la nouvelle image
                    $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFileName = $slugger->slug($originalName);
                    $newFileName = $safeFileName.'-'.uniqid().'.'.$imageFile->guessExtension();
            
                    try {
                        $imageFile->move(
                            $this->getParameter('image_dir'),
                            $newFileName
                        );
                    } catch(FileException $exception) {}
            
                    $productImageEntity->setImagePath($newFileName);
                    $productImageEntity->setProduct($product); // au cas où
                }
            
                if ($delete && $productImageEntity) {
                    // Supprimer l’image de la BDD et potentiellement du disque
                    $entityManager->remove($productImageEntity);
                }
            }            

            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été modifié.');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('danger', 'Le produit a bien été supprimé.');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/add/product/{id}/stock', name: 'app_product_add_stock', methods: ['POST', 'GET'])]
    public function addStock($id, EntityManagerInterface $entityManager, Request $request, ProductRepository $productRepository): Response
    {
        $addStock = new AddProductHistory();
        $form = $this->createForm(AddProductHistoryType::class, $addStock);
        $form->handleRequest($request);

        $product = $productRepository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($addStock->getQuantity()>0){
                $newQuantity = $product->getStock() + $addStock->getQuantity();
                $product->setStock($newQuantity);

                $addStock->setCreatedAt(new \DateTimeImmutable());
                $addStock->setProduct($product);

                $entityManager->persist($addStock);
                $entityManager->flush();

                $this->addFlash('success', 'Le stock de votre produit a été mis à jour.');

                return $this->redirectToRoute('app_product_index');
            } else {
                $this->addFlash('danger', 'Le stock ne doit pas être inférieur à 0.');

                return $this->redirectToRoute('app_product_add_stock', ['id' => $product->getId()]);
            }
        }

        return $this->render('product/addStock.html.twig',
            [
                'form' => $form->createView(),
                'product' => $product
            ]
        );
    }

    #[Route('/add/product/{id}/stock/history', name: 'app_product_add_stock_history', methods: ['GET'])]
    public function productAddHistory($id, ProductRepository $productRepository, AddProductHistoryRepository $addProductHistoryRepository): Response
    {
        $product = $productRepository->find($id);
        $productAddedHistory = $addProductHistoryRepository->findBy(['product' => $product], ['id' => 'DESC']);

        return $this->render('product/addedStockHistoryShow.html.twig', [
            'productsAdded' => $productAddedHistory
        ]);
    }
}
