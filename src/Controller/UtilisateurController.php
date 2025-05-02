<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Form\UtilisateurEditType;
use App\Form\SearchUtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/utilisateur')]
class UtilisateurController extends AbstractController
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/', name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(Request $request, UtilisateurRepository $utilisateurRepository, RoleRepository $roleRepository): Response
    {
        // Seuls les RH peuvent voir tous les utilisateurs
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        $form = $this->createForm(SearchUtilisateurType::class);
        $form->handleRequest($request);

        $utilisateurs = [];
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $query = $data['query'] ?? null;
            $role = $data['role'] ?? null;
            
            $utilisateurs = $utilisateurRepository->search($query, $role);
        } else {
            $utilisateurs = $utilisateurRepository->findAll();
        }

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'roles' => $roleRepository->findAll(),
            'search_form' => $form->createView(),
        ]);
    }

    #[Route('/search', name: 'app_utilisateur_search', methods: ['GET'])]
    public function search(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        // Seuls les RH peuvent effectuer des recherches
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        $query = $request->query->get('query');
        $roleId = $request->query->get('role');

        $utilisateurs = $utilisateurRepository->searchDynamic($query, $roleId);

        $results = [];
        foreach ($utilisateurs as $utilisateur) {
            $results[] = [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'cin' => $utilisateur->getCin(),
                'role' => $utilisateur->getRole() ? $utilisateur->getRole()->getRoleName() : '',
                'showUrl' => $this->generateUrl('app_utilisateur_show', ['id' => $utilisateur->getId()]),
                'editUrl' => $this->generateUrl('app_utilisateur_edit', ['id' => $utilisateur->getId()]),
            ];
        }

        return new JsonResponse($results);
    }

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, RoleRepository $roleRepository): Response
    {
        // Seuls les RH peuvent créer des utilisateurs
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setPassword($hashedPassword);

            $photoFile = $form->get('photo')->getData();
            $photoCameraData = $request->request->get('photo_camera');

            // Si une photo a été capturée par la webcam
            if (!empty($photoCameraData)) {
                // Traiter l'image base64
                $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoCameraData));
                $newFilename = 'webcam-' . uniqid() . '.png';
                $photoPath = $this->getParameter('photos_directory') . '/' . $newFilename;
                
                try {
                    file_put_contents($photoPath, $photoData);
                    $utilisateur->setPhoto($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de la photo: ' . $e->getMessage());
                }
            }
            // Sinon, si une photo a été téléchargée normalement
            elseif ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    $utilisateur->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo.');
                }
            }

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'roles' => $roleRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): Response
    {
        // Les utilisateurs ne peuvent voir que leur propre profil, sauf les RH
        if (!$this->isGranted('ROLE_RH') && $utilisateur->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException('Accès refusé.');
        }

        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager, SluggerInterface $slugger, RoleRepository $roleRepository): Response
    {
        // Seuls les RH peuvent modifier tous les utilisateurs
        // Les autres ne peuvent modifier que leur propre profil
        if (!$this->isGranted('ROLE_RH') && $this->getUser() !== $utilisateur) {
            throw new AccessDeniedException('Accès refusé.');
        }

        $form = $this->createForm(UtilisateurType::class, $utilisateur, [
            'is_edit' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $photoFile */
            $photoFile = $form->get('photo')->getData();
            $photoCameraData = $request->request->get('photo_camera');
            
            // Gérer la photo capturée par la webcam
            if (!empty($photoCameraData)) {
                // Traiter l'image base64
                $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoCameraData));
                $newFilename = 'webcam-' . uniqid() . '.png';
                $photoPath = $this->getParameter('photos_directory') . '/' . $newFilename;
                
                try {
                    // Supprimer l'ancienne photo si elle existe
                    if ($utilisateur->getPhoto()) {
                        $oldPhotoPath = $this->getParameter('photos_directory').'/'.$utilisateur->getPhoto();
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }
                    
                    file_put_contents($photoPath, $photoData);
                    $utilisateur->setPhoto($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de la photo: ' . $e->getMessage());
                }
            }
            // Sinon, si une photo a été téléchargée normalement
            elseif ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );

                    // Supprimer l'ancienne photo si elle existe
                    if ($utilisateur->getPhoto()) {
                        $oldPhotoPath = $this->getParameter('photos_directory').'/'.$utilisateur->getPhoto();
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }
                    
                    $utilisateur->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Les modifications ont été enregistrées avec succès.');
            
            // Rediriger vers la liste pour les RH, vers le profil pour les autres
            if ($this->isGranted('ROLE_RH')) {
                return $this->redirectToRoute('app_utilisateur_index');
            } else {
                return $this->redirectToRoute('app_utilisateur_show', ['id' => $utilisateur->getId()]);
            }
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'roles' => $roleRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        // Seuls les RH peuvent supprimer des utilisateurs
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            // Supprimer la photo si elle existe
            if ($utilisateur->getPhoto()) {
                $photoPath = $this->getParameter('photos_directory').'/'.$utilisateur->getPhoto();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }

            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }
}
