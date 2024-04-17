<?php

namespace App\Infrastructure\Eloquent\Repository;

use App\Domain\Model\UserModel;
use App\Domain\Model\UserStatus;
use App\Domain\Repository\UserRepository;
use App\Infrastructure\Eloquent\Models\User;
use Carbon\Carbon;

class UserRepositoryEloquent implements UserRepository
{
    public function hasEmailRegistered(string $email): bool
    {
        $entity = User::whereEmail($email)->first();
        return $entity !== null;
    }

    public function insert(
        string $name,
        string $email,
        string $password
    ): UserModel {
        $entity = new User();
        $entity->name = $name;
        $entity->email = $email;
        $entity->password = $password;
        $entity->save();

        return new UserModel(
            $entity->id,
            $name,
            $email,
            $entity->created_at,
            $entity->updated_at,
            UserStatus::from($entity->status)
        );
    }

}
