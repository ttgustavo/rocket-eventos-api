<?php

namespace App\Presenter\Http\Controllers\Api\Client\Attendee;

use App\Domain\Model\EventStatus;
use App\Domain\Repository\AttendeeRepository;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UnsubscribeAttendeeController extends ApiController
{
    private AttendeeRepository $attendeeRepository;
    private EventRepository $eventRepository;

    public function __construct(AttendeeRepository $attendeeRepository, EventRepository $eventRepository)
    {
        $this->attendeeRepository = $attendeeRepository;
        $this->eventRepository = $eventRepository;
    }

    #[Delete(
        path: '/events/{id}/attendees',
        description: 'Unsubscribe user from the event with the specified ID in the route.',
        summary: 'Unsubscribe to an event',
        security: [
            [
                'sanctum' => []
            ]
        ],
        tags: ['Subscriptions'],
        parameters: [
            new Parameter(name: 'id', in: 'path', required: true, schema: new Schema(type: 'integer'))
        ],
        responses: [
            new Response(response: HttpResponse::HTTP_OK, description: 'Already unsubscribed to the event.'),
            new Response(response: HttpResponse::HTTP_NO_CONTENT, description: 'Unsubscribed to the event.'),
            new Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Failed to unsubscribe. There are two reasons to that, specified by the code in body:<br>' .
                '- 0: validation<br>' .
                '- 1: event does not exist or event has done status',
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

        $isEventDone = $event->status == EventStatus::Done;
        if ($isEventDone) return parent::responseBadRequest(['code' => 1]);

        $userId = Auth::id();

        $hasUserInEvent = $this->attendeeRepository->hasUserInEvent($eventId, $userId);
        if ($hasUserInEvent === false) return parent::responseBadRequest(['code' => 1]);

        $this->attendeeRepository->remove($eventId, $userId);
        return parent::responseNoContent();
    }
}
