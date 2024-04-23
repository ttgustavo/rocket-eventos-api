<?php

namespace App\Domain\Repository;

interface AttendeeRepository
{
    public function isAlreadyAnAttendee(int $eventId, int $userId): bool;

    public function create(int $eventId, int $userId): void;
}
