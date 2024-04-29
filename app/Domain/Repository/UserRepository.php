<?php

namespace App\Domain\Repository;
use App\Domain\Model\UserModel;

interface UserRepository
{
    public function hasEmailRegistered(string $email): bool;
    public function getPasswordFromUser(int $id): string;

    public function isBanned(string $email): bool;

    public function insert(string $name, string $email, string $password): UserModel;

    public function update(int $id, ?string $name, ?string $email, ?string $password): UserModel;

}
