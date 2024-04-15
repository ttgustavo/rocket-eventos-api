<?php

namespace App\Domain\Model;

use Carbon\Carbon;

class EventModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $details,
        public readonly Carbon $subscriptionStart,
        public readonly Carbon $subscriptionEnd,
        public readonly Carbon $presentationAt,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
        public readonly EventStatus $status,
    ) {
    }
}