<?php

namespace App\Infrastructure\Eloquent\Repository;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Repository\EventRepository;
use App\Infrastructure\Eloquent\Models\Event;
use Carbon\Carbon;

class EventRepositoryEloquent implements EventRepository
{
    public function hasEventWithSlug(string $slug): bool
    {
        $event = Event::whereSlug($slug)->first(['id']);
        return $event !== null;
    }

    public function create(
        string $name,
        string $slug,
        string $details,
        string $subscriptionDateStart,
        string $subscriptionDateEnd,
        string $presentationAt
    ): EventModel {
        $event = new Event();
        $event->name = $name;
        $event->slug = $slug;
        $event->details = $details;
        $event->subscription_date_start = Carbon::parse($subscriptionDateStart);
        $event->subscription_date_end = Carbon::parse($subscriptionDateEnd);
        $event->presentation_at = Carbon::parse($presentationAt);
        $event->save();

        return new EventModel(
            $event->id,
            $name,
            $slug,
            $details,
            $event->subscription_date_start,
            $event->subscription_date_end,
            $event->presentation_at,
            $event->created_at,
            $event->updated_at,
            EventStatus::from($event->status)
        );
    }
}