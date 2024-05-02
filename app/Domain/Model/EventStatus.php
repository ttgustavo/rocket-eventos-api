<?php

namespace App\Domain\Model;

use OpenApi\Attributes\Schema;

#[Schema]
enum EventStatus : int
{
    case Draft = 0;
    case Created = 1;
    case SubscriptionsOpen = 2;
    case SubscriptionsEnded = 3;
    case Done = 4;
    case Canceled = 5;
}
