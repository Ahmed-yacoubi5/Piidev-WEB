<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UserController extends AbstractController
{
    // BACK OFFICE *****************************************************
    
    // AFFICHAGE Users
    #[Route('/backShowuser', name: 'back_showuser')]
    public function ShowUser(UserRepository $ur): Response
    {
        $list = $ur->findAll();
        return $this->render('backOffice/user/UserShow.html.twig', [
            'users' => $list, // Le tableau est envoyé sous le nom "liste" et non "users"
        ]);
    }


    // AJOUT User
    #[Route('/backAdduser', name: 'ajouter_user')]
    public function ajouterUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de la photo
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $roles = $form->get('roles')->getData(); 
            $user->setRoles($roles); 
            $photoFile = $form->get('photoDeProfile')->getData();
            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $user->setPhotoDeProfile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo');
                }
            }

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('back_showuser');
        }

        return $this->render('backOffice/user/UserAdd.html.twig', [
            'form' => $form->createView(),
            'titre' => "Ajouter"
        ]);
    }

    // MODIFIER User
    #[Route('/backEdituser/{id}', name: 'edit_user')]
    public function modifierUser(
        $id,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $ur,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $ur->find($id);
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de la photo
            $photoFile = $form->get('photoDeProfile')->getData();
            if ($photoFile) {
                // Supprimer l'ancienne photo
                if ($user->getPhotoDeProfile()) {
                    $oldPhotoPath = $this->getParameter('images_directory').'/'.$user->getPhotoDeProfile();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $user->setPhotoDeProfile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo');
                }
            }

            $em->flush();
            return $this->redirectToRoute('back_showuser');
        }

        return $this->render('backOffice/user/UserEdit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'titre' => "Modifier"
        ]);
    }

    // DELETE User
    #[Route('/backDeluser/{id}', name: 'delete_user')]

    public function DeleteUser($id, UserRepository $ur, EntityManagerInterface $em): Response
    {
        $user = $ur->find($id);
        
        // Supprimer la photo si elle existe
        if ($user->getPhotoDeProfile()) {
            $photoPath = $this->getParameter('images_directory').'/'.$user->getPhotoDeProfile();
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('back_showuser');
    }
} 