<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
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

class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $data = $categoryRepository->findBy([], ['id'=>'DESC']);
        $categories = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            12
        );

       /*  $categories = $categoryRepository->findAll();
 */
        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show')]
    public function show(Category $category, ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(['category' => $category]);

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    #[Route('/admin/category/new', name: 'app_category_new')]
    public function addCategory(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger) : Response 
    {
        $category = new Category();

        $form = $this->createForm(CategoryFormType::class, $category, [
            'csrf_protection' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('categories_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
    
                $category->setImage($newFilename);
            }
    
            $entityManager->persist($category);
            $entityManager->flush();
    
            $this->addFlash('success', 'La catégorie a bien été ajoutée.');
            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/new.html.twig', ['form'=>$form->createView()]);

    }

    #[Route('/admin/category/{id}/update', name: 'app_category_update')]
    public function updateCategory(Category $category, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger) : Response
    {
        $form = $this->createForm(CategoryFormType::class, $category, [
            'csrf_protection' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('categories_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
    
                $category->setImage($newFilename);
            }
    
            $entityManager->persist($category);
            $entityManager->flush();
    
            $this->addFlash('success', 'La catégorie a bien été modifiée.');
            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/update.html.twig', ['form'=>$form->createView()]);
    }

    #[Route('/admin/category/{id}/delete', name: 'app_category_delete')]
    public function deleteCategory(Request $request, Category $category, EntityManagerInterface $entityManager) : Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('danger', 'La catégorie a bien été supprimée.');
        }

        return $this->redirectToRoute('app_category');
    }

}
