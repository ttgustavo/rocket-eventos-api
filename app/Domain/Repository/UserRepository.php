<?php

namespace App\Domain\Repository;
use App\Domain\Model\UserModel;

interface UserRepository
{
    public function hasEmailRegistered(string $email): bool;

    public function insert(string $name, string $email, string $password): UserModel;

}