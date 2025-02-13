<?php

namespace App\Controller;

use App\Form\UpdatePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AccountManagerController extends AbstractController
{
    #[Route('/mon-compte/update-password', name: 'app_update_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté');
        }

        $form = $this->createForm(UpdatePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('repeatPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel n\'est pas valide');
                return $this->redirectToRoute('app_update_password');
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas');
                return $this->redirectToRoute('app_update_password');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();
            $this->addFlash('success', 'Votre mot de passe a été mis à jour !');
            return $this->redirectToRoute('app_mon_compte');
        }
        return $this->render('mon_compte/update_password.html.twig', [
            'controller_name' => 'AccountManagerController',
            'form' => $form->createView(),
        ]);
    }
}
