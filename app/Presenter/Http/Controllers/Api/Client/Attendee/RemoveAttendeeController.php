<?php

namespace App\Presenter\Http\Controllers\Api\Client\Attendee;

use App\Domain\Model\EventStatus;
use App\Domain\Repository\AttendeeRepository;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RemoveAttendeeController extends ApiController
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

        $isEventDone = $event->status == EventStatus::Done;
        if ($isEventDone) return parent::responseBadRequest(['code' => 1]);

        $userId = Auth::id();

        $hasUserInEvent = $this->attendeeRepository->hasUserInEvent($eventId, $userId);
        if ($hasUserInEvent === false) return parent::responseBadRequest(['code' => 1]);

        $this->attendeeRepository->remove($eventId, $userId);
        return parent::responseNoContent();
    }
}
