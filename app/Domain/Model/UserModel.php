<?php

namespace App\Domain\Model;

use Carbon\Carbon;

class UserModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
        public readonly UserStatus $status
    ) {
    }
}