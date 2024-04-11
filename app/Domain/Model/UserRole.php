<?php

namespace App\Domain\Model;

enum UserRole : int
{
    case USER = 0;
    case ADMIN = 1;
}