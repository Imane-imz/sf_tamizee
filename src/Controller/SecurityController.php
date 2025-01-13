<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérifiez si un formulaire a été soumis
        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('_csrf_token');
            $serverToken = $csrfTokenManager->getToken('authenticate')->getValue();
        
            dump('Server Token: ' . $serverToken);
            dump('Submitted Token: ' . $submittedToken);
            die;
        
            if ($csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $submittedToken))) {
                $this->addFlash('success', 'CSRF token is valid!');
            } else {
                $this->addFlash('error', 'Invalid CSRF token!');
            }
        }

        // Déboguer pour voir le jeton côté serveur
        $serverToken = $csrfTokenManager->getToken('authenticate')->getValue();
        dump($serverToken); // Vous pouvez vérifier si ce jeton correspond à celui généré dans votre template.

        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
