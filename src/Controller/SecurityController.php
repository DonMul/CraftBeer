<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('admin');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/admin/change-password")
     */
    public function changePassword(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('security/changePassword.html.twig');
    }

    /**
     * @Route("/service/admin/user/change-password")
     */
    public function changePasswordSubmit(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $password = $request->request->get('password1');
        $passwordRepeat = $request->request->get('password2');

        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (($user instanceof User) === false) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Gebruiker niet gevonden'
                ],
            ]);
        }

        if ($password != $passwordRepeat) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Je wachtwoorden komen niet overeen '
                ],
            ]);
        }

        $user->setPassword($encoder->encodePassword($user, $password));

        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'response' => [
                'message' => 'Wachtwoord veranderd'
            ]
        ]);
    }
}
