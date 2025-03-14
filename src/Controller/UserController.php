<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users'=>$userRepository->findAll(),
        ]);
    }

    #[Route('/admin/user/{id}/delete', name: 'app_user_delete')]
    public function deleteUser(EntityManagerInterface $entityManager, $id, UserRepository $userRepository): Response
    {
        $userFind = $userRepository->find($id);

        $entityManager->remove($userFind);
        $entityManager->flush();

        $this->addFlash('danger', 'Cet utilisateeur a bien été supprimé');

        return $this->redirectToRoute('app_user');
    }
}
