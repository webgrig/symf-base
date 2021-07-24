<?php

namespace App\Service\User;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface UserServiceInterface
{
    /**
     * @param User $user
     * @return FormInterface
     */
    public function createForm(User $user): object;

    /**
     * @return User[]
     */
    public function getAll(): array;

    /**
     * @param int $id
     * @return User
     */
    public function getOne(int $id): object;

    /**
     * @param User $user
     */
    public function prepareEntity(User $user): void;

    /**
     * @param User $user
     * @return Role[]|Collection
     */
    public function updateRolesCollection(User $user): array;

    /**
     * @param User $user
     * @return User
     */
    public function saveImg(User $user): object;

    /**
     * @param User $user
     */
    public function deleteImg(User $user): void;

    /**
     * @param User $user
     * @return User|object
     */
    public function save(User $user): object;

    public function getCratedEntityId();

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse;
}