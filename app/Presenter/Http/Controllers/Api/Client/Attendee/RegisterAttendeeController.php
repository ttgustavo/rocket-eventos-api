<?php

namespace App\Presenter\Http\Controllers\Api\Client\Attendee;

use App\Domain\Model\EventStatus;
use App\Domain\Repository\AttendeeRepository;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterAttendeeController extends ApiController
{
    private AttendeeRepository $attendeeRepository;
    private EventRepository $eventRepository;

    public function __construct(AttendeeRepository $attendeeRepository, EventRepository $eventRepository)
    {
        $this->attendeeRepository = $attendeeRepository;
        $this->eventRepository = $eventRepository;
    }

    public function __invoke(Request $request, int|string $eventId): JsonResponse
    {
        $validation = RegisterAttendeeValidation::validateParams($eventId);
        if ($validation->fails()) return parent::responseBadRequest(['code' => 0]);

        $event = $this->eventRepository->getById($eventId);
        if (is_null($event)) return parent::responseBadRequest(['code' => 1]);

        $dateTimeNow = Carbon::now();
        $isEventSubscriptionsOpen = $dateTimeNow->betweenIncluded($event->subscriptionStart, $event->subscriptionEnd);
        if ($isEventSubscriptionsOpen === false) return parent::responseBadRequest(['code' => 1]);

        $isEventSubscriptionsOpen = $event->status == EventStatus::SubscriptionsOpen;
        if ($isEventSubscriptionsOpen === false) return parent::responseBadRequest(['code' => 1]);

        $userId = Auth::id();

        $isAlreadyAnAttendee = $this->attendeeRepository->isAlreadyAnAttendee($eventId, $userId);
        if ($isAlreadyAnAttendee) return parent::responseOk();

        $this->attendeeRepository->create($eventId, $userId);

        return parent::responseCreated();
    }
}
