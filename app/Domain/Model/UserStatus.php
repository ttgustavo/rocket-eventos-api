<?php

namespace App\Domain\Model;

use OpenApi\Attributes\Schema;

#[Schema]
enum UserStatus : int {
    case Registered = 0;
    case Banned = 1;
}
