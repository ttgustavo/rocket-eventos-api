<?php

namespace App\Domain\Model;

enum UserStatus : int {
    case Registered = 0;
    case Banned = 1;
}