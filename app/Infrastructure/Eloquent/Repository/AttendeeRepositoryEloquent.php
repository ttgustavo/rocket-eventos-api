<?php

namespace App\Infrastructure\Eloquent\Repository;

use App\Domain\Repository\AttendeeRepository;
use App\Infrastructure\Eloquent\Models\Attendee;

class AttendeeRepositoryEloquent implements AttendeeRepository
{
    public function hasUserInEvent(int $eventId, int $userId): bool
    {
        return Attendee::whereEventId($eventId)->whereUserId($userId)->exists();
    }

    public function create(int $eventId, int $userId): void
    {
        $attendee = new Attendee();
        $attendee->event_id = $eventId;
        $attendee->user_id = $userId;
        $attendee->save();
    }
}
