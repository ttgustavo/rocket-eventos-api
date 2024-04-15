<?php

namespace App\Domain\Model;

enum EventStatus : int
{
    case Draft = 0;
    case SubscriptionsOpen = 1;
    case SubscriptionsEnded = 2;
    case Done = 3;
    case Canceled = 4;
}