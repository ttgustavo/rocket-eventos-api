<?php

namespace App\Infrastructure\Eloquent\Repository;

use App\Domain\Model\AttendeeStatus;
use App\Domain\Model\UserEventAttendee;
use App\Domain\Repository\AttendeeRepository;
use App\Infrastructure\Eloquent\Models\Attendee;
use App\Infrastructure\Eloquent\Models\Event;
use App\Infrastructure\Eloquent\Models\User;

class AttendeeRepositoryEloquent implements AttendeeRepository
{
    public function hasUserInEvent(int $eventId, int $userId): bool
    {
        return Attendee::whereEventId($eventId)->whereUserId($userId)->exists();
    }

    public function getEventsFromUser(int $userId): array
    {
        $user = (new User)->find($userId);

        /** @var Event[] $eventEntities */
        $attributes = ['events.*', 'attendees.status as attendee_status'];
        $eventEntities = $user->events()->orderByDesc('updated_at')->get($attributes)->all();

        $predicate = function (Event $event): UserEventAttendee {
            $model = $event->toDomainModel();
            $attendeeStatus = AttendeeStatus::from($event->attendee_status);
            return new UserEventAttendee($model, $attendeeStatus);
        };

        return array_map($predicate, $eventEntities);
    }

    public function create(int $eventId, int $userId): void
    {
        $attendee = new Attendee();
        $attendee->event_id = $eventId;
        $attendee->user_id = $userId;
        $attendee->save();
    }

    public function remove(int $eventId, int $userId): void
    {
        Attendee::whereEventId($eventId)->whereUserId($userId)->delete();
    }
}
