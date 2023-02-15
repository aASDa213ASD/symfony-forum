<?php
namespace App\Service;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAllPosts()
    {
        $repository = $this->entityManager->getRepository(Post::class);
        return $repository->findAll();
    }
}