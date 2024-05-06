<?php

namespace App\Presenter\Http\Controllers\Api\Admin\Events;

use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DeleteEventController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Delete(
        path: '/admin/events/{id}',
        summary: 'Deletes an event.',
        security: [[
            'sanctum' => []
        ]],
        tags: ['Admin'],
        parameters: [
            new Parameter(
                name: 'id',
                description: 'The id of the event.',
                in: 'path',
                required: true,
                schema: new Schema(type: 'integer'))
        ],
        responses: [
            new Response(
                response: HttpResponse::HTTP_NO_CONTENT,
                description: 'The event was deleted.'
            ),
            new Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'When "code" is zero, means validation. When is one, means that the event does not exists.',
                content: new JsonContent(
                    properties: [
                        new Property(property: 'code', type: 'integer', default: '0')
                    ]
                )
            ),
            new Response(
                response: HttpResponse::HTTP_UNAUTHORIZED,
                description: 'The admin is not authenticated.'
            ),
            new Response(
                response: HttpResponse::HTTP_FORBIDDEN,
                description: 'The user is not an admin/super.'
            ),
        ]
    )]
    public function __invoke(Request $request, int|string $eventId): JsonResponse
    {
        $areParamsValid = $this->validateParams($eventId);
        if ($areParamsValid === false) return parent::responseBadRequest(['code' => 0]);

        $doesEventExists = $this->repository->hasEventWithId($eventId);
        if ($doesEventExists === false) return parent::responseBadRequest(['code' => 1]);

        $this->repository->delete($eventId);

        return parent::responseNoContent();
    }

    private function validateParams(int|string $id): bool
    {
        $inputs = ['id' => $id];
        $rules = [
            'id' => ['required', 'int', 'min:0'],
        ];

        return Validator::make($inputs, $rules)->passes();
    }
}
