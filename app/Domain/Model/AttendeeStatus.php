<?php

namespace App\Domain\Model;

enum AttendeeStatus : int
{
    case Subscribed = 0;
    case CheckinDone = 1;
    case Banned = 2;
}
