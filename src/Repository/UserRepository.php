<?php

namespace App\Repository;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserRepositoryInterface
{
    private $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct($registry, User::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param int $userId
     * @return User
     */
    public function findOne(int $userId): object
    {
        return parent::find($userId);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return parent::findAll();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function setSave(User $user)
    {
        $this->entityManager->flush();
    }


    public function findUserRoles($id): array{
        $roles = $this->createQueryBuilder('u')
            ->select('u.roles')
            -join(Role::class, '')
            ->where('u.id  :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
            ;
    }
    public function findAllRoles()
    {
        $roles = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :val')
            ->setParameter('val', "%ROLE_SUPER%")
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()[0]
            ->getRoles()
            ;
        $rolesCollection = [];
        foreach ($roles as $role)
        {
            $node = new Role();
            $node->setId(1);
            $node->setTitle($role);
            $node->setValue($role);

            $rolesCollection[] = $node;
        }
        return $rolesCollection;
    }
}
