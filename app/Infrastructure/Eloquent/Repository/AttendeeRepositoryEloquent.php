<?php

namespace App\Infrastructure\Eloquent\Repository;

use App\Domain\Repository\AttendeeRepository;
use App\Infrastructure\Eloquent\Models\Attendee;

class AttendeeRepositoryEloquent implements AttendeeRepository
{
    public function isAlreadyAnAttendee(int $eventId, int $userId): bool
    {
        return Attendee::whereEventId($eventId)->whereUserId($userId)->exists();
    }

    public function create(int $eventId, int $userId): void
    {
        // TODO: Implementation
    }
}
