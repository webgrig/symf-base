<?php

namespace App\Repository;

use App\Entity\Post;
use App\Service\File\FileManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    private $em;
    private $fm;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager, FileManagerInterface $fileManagerService)
    {
        $this->em = $manager;
        $this->fm = $fileManagerService;
        parent::__construct($registry, Post::class);
    }

    /**
     * @param Post $post
     * @param UploadedFile $file
     * @return $this|Post
     */
    public function setCreatePost(Post $post, UploadedFile $file): PostRepositoryInterface
    {
        $this->em->persist($post);
        $this->em->flush();

        return $this;
    }

    /**
     * @param Post $post
     * @param UploadedFile $file
     * @return $this|Post
     */
    public function setSavePost(Post $post, UploadedFile $file): PostRepositoryInterface
    {
        $this->em->flush();

        return $this;
    }
}