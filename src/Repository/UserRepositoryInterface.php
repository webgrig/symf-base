<?php


namespace App\Repository;


use App\Entity\User;

interface UserRepositoryInterface
{

    /**
     * @param User $user
     * @return mixed
     */
    public function setSave(User $user);

    /**
     * @param int $userId
     * @return User
     */
    public function findOne(int $userId): object;

    /**
     * @return User[]
     */
    public function getAll(): array;
}