<?php

namespace App\Controller;

use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ForumController extends AbstractController
{   
    private $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * @Route("/forum", name="forum")
     */
    public function index(Security $security): Response
    {
        $user = $security->getUser();
        $username = $user ? $user->getUsername() : 'guest';
        $posts = $this->postService->getAllPosts();

        return $this->render('forum.html.twig', [
            'username' => $username,
            'posts' => $posts,
        ]);
    }
}