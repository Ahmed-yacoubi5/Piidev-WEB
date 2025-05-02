<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category_index', methods: ['GET'])]
    public function index(Request $request, CategoryRepository $categoryRepository): Response
    {
        // Check if user has admin role
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Access Denied: You do not have permission to view categories.');
        }
        
        $searchCriteria = [];
        
        // Get search parameters
        if ($request->query->get('search')) {
            $searchCriteria['name'] = $request->query->get('name');
            
            $categories = $categoryRepository->findBySearch($searchCriteria);
        } else {
            $categories = $categoryRepository->findAll();
        }
        
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'searchCriteria' => $searchCriteria,
        ]);
    }
    
    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        // Check if user has admin role
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Access Denied: You do not have permission to create categories.');
        }
        
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if category with same name already exists
            $existingCategory = $categoryRepository->findOneBy(['name' => $category->getName()]);
            if ($existingCategory) {
                $this->addFlash('category_error', 'A category with this name already exists.');
                return $this->render('category/new.html.twig', [
                    'category' => $category,
                    'form' => $form->createView(),
                ]);
            }
            
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('category_success', 'Category has been created successfully!');
            
            return $this->redirectToRoute('app_category_index');
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        // Check if user has admin role
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Access Denied: You do not have permission to view this category.');
        }
        
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        // Check if user has admin role
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Access Denied: You do not have permission to edit categories.');
        }
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if category with same name already exists (excluding current category)
            $existingCategory = $categoryRepository->findOneBy(['name' => $category->getName()]);
            if ($existingCategory && $existingCategory->getId() !== $category->getId()) {
                $this->addFlash('category_error', 'A category with this name already exists.');
                return $this->render('category/edit.html.twig', [
                    'category' => $category,
                    'form' => $form->createView(),
                ]);
            }
            
            $entityManager->flush();

            $this->addFlash('category_success', 'Category has been updated successfully!');
            
            return $this->redirectToRoute('app_category_index');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/delete', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        // Check if user has admin role
        if (!$this->isGranted('ROLE_RH')) {
            throw new AccessDeniedException('Access Denied: You do not have permission to delete categories.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            // Check if category has events
            if ($category->getEvents()->count() > 0) {
                $this->addFlash('category_error', 'Cannot delete this category because it contains events. Please delete or reassign the events first.');
                return $this->redirectToRoute('app_category_index');
            }
            
            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('category_success', 'Category deleted successfully.');
        }

        return $this->redirectToRoute('app_category_index');
    }
    
    #[Route('/test', name: 'app_category_test')]
    public function test(): Response
    {
        return new Response('Hello, World!');
    }
} 