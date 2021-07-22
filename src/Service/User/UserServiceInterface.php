<?php

namespace App\Service\User;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

interface UserServiceInterface
{
    /**
     * @param User $user
     * @return Form
     */
    public function createForm(User $user): object;

    /**
     * @return array
     */
    public function getAllEntities(): array;

    /**
     * @param int $id
     * @return User
     */
    public function getEntity(int $id): object;

    /**
     * @param User $user
     */
    public function prepareEntity(User $user): void;

    /**
     * @param User $user
     * @return Role
     */
    public function updateRolesCollection(User $user): object;

    /**
     * @param User $user
     */
    public function deleteImg(User $user): void;

    /**
     * @param User $user
     * @return $this|string
     */
    public function save(User $user);

    public function getCratedEntityId();

    /**
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response;
}