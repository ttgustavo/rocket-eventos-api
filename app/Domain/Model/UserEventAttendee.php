<?php

namespace App\Domain\Model;

readonly class UserEventAttendee
{
    public function __construct(public EventModel $event, public AttendeeStatus $attendeeStatus)
    {
    }
}
