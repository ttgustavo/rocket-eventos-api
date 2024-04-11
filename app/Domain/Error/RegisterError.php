<?php

namespace App\Domain\Error;

enum RegisterError implements Error
{
    case Validation;
    case EmailAlreadyExists;
}