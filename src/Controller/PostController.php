<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Like;
use App\Form\PostType;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class PostController extends AbstractController
{
    #[Route('/', name: 'forum_index', methods: ['GET', 'POST'])]
    public function index(Request $request, PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        // Create a new Post and set the current user as author immediately
        $post = new Post();
        $post->setAuthor($this->getUser());
        
        $postForm = $this->createForm(PostType::class, $post);
        $postForm->handleRequest($request);
    
        if ($postForm->isSubmitted() && $postForm->isValid()) {
            try {
                // Handle file upload manually
                $imageFile = $postForm->get('imageFile')->getData();
                if ($imageFile) {
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $post->setImage($newFilename);
                }
    
                $em->persist($post);
                $em->flush();
    
                $this->addFlash('success', 'Post added successfully.');
                return $this->redirectToRoute('forum_index');
            } catch (\Exception $e) {
                dd('Error: ' . $e->getMessage());
            }
        }
    
        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('post/index.html.twig', [
            'postForm' => $postForm->createView(),
            'posts'    => $posts,
        ]);
    }
    
    #[Route('/{id}/update-inline', name: 'forum_update_inline', methods: ['POST'])]
    public function updateInline(Post $post, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if ($user !== $post->getAuthor()) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }


        $title = $request->request->get('title');
        $content = $request->request->get('content');

        if (empty($title) || empty($content)) {
            return new JsonResponse(['error' => 'Title and content cannot be empty.'], Response::HTTP_BAD_REQUEST);
        }

        $post->setTitle($title);
        $post->setContent($content);

        $imageFile = $request->files->get('imageFile');
        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();
            try {
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Failed to upload image.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $post->setImage($newFilename);
        }

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'title'   => $post->getTitle(),
            'content' => nl2br($post->getContent()),
            'image'   => $post->getImage() ? '/uploads/images/' . $post->getImage() : null
        ]);
    }


    
    #[Route('/{id}/delete', name: 'forum_delete', methods: ['POST'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        // Only allow the author to delete the post.
        if ($this->getUser() !== $post->getAuthor()) {
            $this->addFlash('error', 'You are not authorized to delete this post.');
            return $this->redirectToRoute('forum_index');
        }
    
        // Validate CSRF token
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
    
            $this->addFlash('success', 'Post deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }
    
        return $this->redirectToRoute('forum_index');
    }
    
    #[Route('/{id}/like', name: 'forum_like', methods: ['POST'])]
    public function like(Post $post, LikeRepository $likeRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'You must be logged in.'], Response::HTTP_FORBIDDEN);
        }
    
        $existingLike = $likeRepository->findOneBy([
            'post' => $post,
            'user' => $user,
        ]);
    
        if ($existingLike) {
            $em->remove($existingLike);
            $em->flush();
            return new JsonResponse(['liked' => false, 'likeCount' => count($post->getLikes())]);
        } else {
            $like = new Like();
            $like->setPost($post);
            $like->setUser($user);
            $em->persist($like);
            $em->flush();
            return new JsonResponse(['liked' => true, 'likeCount' => count($post->getLikes())]);
        }
    }
    
    #[Route('/{id}/comment', name: 'forum_comment', methods: ['POST'])]
    public function comment(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'You must be logged in to comment.'], Response::HTTP_FORBIDDEN);
        }
    
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
    
        if ($commentForm->isSubmitted() && !$commentForm->isValid()) {
            dd('Form is invalid. Errors:', $commentForm->getErrors(true));
        }
    
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setAuthor($user);
            $comment->setPost($post);
            $em->persist($comment);
            $em->flush();
    
            $this->addFlash('success', 'Comment added successfully.');
        } else {
            $this->addFlash('error', 'Comment cannot be empty or invalid.');
        }
    
        return $this->redirectToRoute('forum_index');
    }
}
