<?php

namespace App\Presenter\Http\Controllers\Api\Admin\Events;

use App\Domain\Model\EventModel;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use App\Presenter\Rules\SlugValidationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/** This controller is for admin only. */
class GetEventController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Get(
        path: '/admin/events/{slug}',
        summary: 'Details of the event.',
        security: [[
            'sanctum' => []
        ]],
        tags: ['Admin'],
        parameters: [
            new Parameter(
                name: 'slug',
                description: 'The slug of the event.',
                in: 'path',
                required: true,
                schema: new Schema(type: 'string', format: 'slug'))
        ],
        responses: [
            new Response(
                response: HttpResponse::HTTP_OK,
                description: 'The event was created and returns all its data.',
                content: new JsonContent(ref: EventModel::class)
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
    public function __invoke(Request $request, string $eventSlug): JsonResponse
    {
        $isValid = $this->validateParams($eventSlug);
        if ($isValid === false) return parent::responseBadRequest(['code' => 0]);

        $event = $this->repository->getBySlug($eventSlug);
        if ($event === null) return parent::responseBadRequest(['code' => 1]);

        return parent::responseOk($event);
    }

    private function validateParams(string $slug): bool
    {
        $data = ['slug' => $slug];
        $rules = [
            'slug' => ['required', 'string', 'min:2', 'max:100', new SlugValidationRule]
        ];

        $validator = Validator::make($data, $rules);
        return $validator->fails() === false;
    }
}
