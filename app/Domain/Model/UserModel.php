<?php

namespace App\Domain\Model;

use Carbon\Carbon;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema]
readonly class UserModel
{
    public function __construct(
        #[Property(type: 'integer', example: '1000')]
        public int        $id,
        #[Property(type: 'string', example: 'John Doe')]
        public string     $name,
        #[Property(type: 'string', example: 'email@email.com')]
        public string     $email,
        #[Property(type: 'string', format: 'date-time', example: new Carbon)]
        public Carbon     $createdAt,
        #[Property(type: 'string', format: 'date-time', example: new Carbon)]
        public Carbon     $updatedAt,
        #[Property(type: 'integer', default: UserStatus::Registered)]
        public UserStatus $status
    ) {
    }
}
