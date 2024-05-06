<?php

namespace App\Infrastructure\Eloquent\Repository;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Pagination\ModelPagination;
use App\Domain\Repository\EventRepository;
use App\Infrastructure\Eloquent\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EventRepositoryEloquent implements EventRepository
{
    public function hasEventWithId(int $id): bool
    {
        $event = Event::whereId($id)->first(['id']);
        return $event !== null;
    }

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

    public function getAll(int $page = 1): ModelPagination
    {
        $query = DB::table('events')
            ->where('status', '=', EventStatus::Created)
            ->orWhere('status', '=', EventStatus::SubscriptionsOpen);

        $total = $query->count('1');
        $pagination = $query->orderBy('updated_at', 'desc')->simplePaginate(page: $page);

        return new ModelPagination(
            $pagination->items(),
            $total,
            $pagination->nextPageUrl(),
            $pagination->previousPageUrl()
        );
    }

    public function list(int $page): ModelPagination
    {
        $events = new Event;
        $total = $events->count('1');

        $pagination = $events
            ->orderBy('created_at', 'desc')
            ->simplePaginate(
                perPage: 10,
                columns: [
                    'id',
                    'name',
                    'slug',
                    'subscription_date_start',
                    'subscription_date_end',
                    'presentation_at',
                    'status',
                    'created_at',
                    'updated_at',
                ],
                page: $page
            );

        return new ModelPagination(
            $pagination->items(),
            $total,
            $pagination->nextPageUrl(),
            $pagination->previousPageUrl()
        );
    }

    public function getById(int $id): ?EventModel
    {
        $entity = Event::whereId($id)->first();
        if (is_null($entity)) return null;

        return $entity->toDomainModel();
    }

    public function getBySlug(string $slug): ?EventModel
    {
        $entity = Event::whereSlug($slug)->first();
        if (is_null($entity)) return null;

        return $entity->toDomainModel();
    }

    public function update(int $id, array $data): ?EventModel
    {
        $event = Event::whereId($id)->first();
        if ($event === null) return null;

        if (key_exists('name', $data)) {
            $event->name = $data['name'];
        }
        if (key_exists('slug', $data)) {
            $event->slug = $data['slug'];
        }
        if (key_exists('details', $data)) {
            $event->details = $data['details'];
        }
        if (key_exists('subscription_date_start', $data)) {
            $event->subscription_date_start = $data['subscription_date_start'];
        }
        if (key_exists('subscription_date_end', $data)) {
            $event->subscription_date_end = $data['subscription_date_end'];
        }
        if (key_exists('presentation_at', $data)) {
            $event->presentation_at = $data['presentation_at'];
        }
        if (key_exists('status', $data)) {
            $event->status = $data['status'];
        }

        $event->save();

        $hasChanges = $event->wasChanged();
        if ($hasChanges === false) return null;

        return $event->toDomainModel();
    }

    public function delete(int $id): void
    {
        Event::whereId($id)->delete();
    }
}
