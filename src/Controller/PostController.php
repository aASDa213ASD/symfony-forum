<?php
namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Post;
use App\Form\PostType;
use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

class PostController extends AbstractController
{   
    private $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * @Route("/post/new", name="post_new")
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/post/create", name="post_create")
     */
    public function create(Request $request): Response
    {   
        if ($this->getUser() != null) {
            $post = new Post();
            $time = new DateTimeImmutable();
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $post->setAuthor($this->getUser()->getUsername());
                $post->setCreatedAt($time);
                $post->setUpdatedAt($time);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($post);
                $entityManager->flush();
    
                return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
            }
    
            return $this->render('post/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        else {
            return $this->redirectToRoute('login');
        }
    }

    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    public function index()
    {
        $posts = $this->postService->getAllPosts();

        return $this->render('forum.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/post/{id}/delete", name="post_delete")
     * @IsGranted("delete", subject="post")
     */
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {   
        if ($this->getUser() != null) {
            $entityManager->remove($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post deleted.');
        }
        return $this->redirectToRoute('forum');
    }

    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager)
    {   
        if ($this->getUser() != null) {
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);
            $time = new DateTimeImmutable();
            if ($form->isSubmitted() && $form->isValid()) {
                $post->setUpdatedAt($time);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($post);
                $entityManager->flush();
            
                return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
            }
            return $this->render('post/edit.html.twig', [
                'post' => $post,
                'form' => $form->createView(),
            ]);
        }
        else {
            return $this->redirectToRoute('forum');
        }
    }

    /**
     * @Route("/post/{id}/data", name="get_post_data")
     */
    public function getPostData(Post $post): JsonResponse
    {
        $data = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'description' => $post->getDescription(),
            'author' => $post->getAuthor(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null,
        ];
        return new JsonResponse($data);
    }
}