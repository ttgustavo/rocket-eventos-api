<?php

namespace App\Infrastructure\Eloquent\Repository;

use App\Domain\Repository\AttendeeRepository;

class AttendeeRepositoryEloquent implements AttendeeRepository
{
    public function isAlreadyAnAttendee(int $eventId, int $userId): bool
    {
        // TODO: Implementation
        return true;
    }

    public function create(int $eventId, int $userId): void
    {
        // TODO: Implementation
    }
}
