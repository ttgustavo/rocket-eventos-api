<?php

namespace App\Domain\Repository;

use App\Domain\Model\UserEventAttendee;

interface AttendeeRepository
{
    public function hasUserInEvent(int $eventId, int $userId): bool;

    /** @return UserEventAttendee[] */
    public function getEventsFromUser(int $userId): array;

    public function create(int $eventId, int $userId): void;

    public function remove(int $eventId, int $userId): void;
}
