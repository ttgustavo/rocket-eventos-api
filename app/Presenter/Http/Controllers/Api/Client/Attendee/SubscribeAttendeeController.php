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
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SubscribeAttendeeController extends ApiController
{
    private AttendeeRepository $attendeeRepository;
    private EventRepository $eventRepository;

    public function __construct(AttendeeRepository $attendeeRepository, EventRepository $eventRepository)
    {
        $this->attendeeRepository = $attendeeRepository;
        $this->eventRepository = $eventRepository;
    }

    #[Post(
        path: '/events/{id}/attendees',
        description: 'Subscribe user to the event with the specified ID in the route.',
        summary: 'Subscribe to an event',
        security: ['bearerAuth' => null],
        tags: ['Subscriptions'],
        parameters: [
            new Parameter(name: 'id', in: 'path', required: true, schema: new Schema(type: 'int'))
        ],
        responses: [
            new Response(response: HttpResponse::HTTP_OK, description: 'Already subscribed in the event.'),
            new Response(response: HttpResponse::HTTP_CREATED, description: 'Subscribed to the event.'),
            new Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Failed to subscribe. There are two reasons to that, specified by the code in body:<br>' .
                '- 0: validation<br>' .
                '- 1: event does not exist or subscriptions period has finished',
                content: [
                    new JsonContent(
                        properties: [
                            new Property(property: 'code', description: "Abc", type: 'int', default: 0),
                        ]
                    ),
                ]
            ),
        ]
    )]
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

        $isAlreadyAnAttendee = $this->attendeeRepository->hasUserInEvent($eventId, $userId);
        if ($isAlreadyAnAttendee) return parent::responseOk();

        $this->attendeeRepository->create($eventId, $userId);

        return parent::responseCreated();
    }
}
