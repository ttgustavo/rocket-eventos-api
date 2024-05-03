<?php

namespace App\Presenter\Http\Controllers\Api\Client\Event;

use App\Domain\Model\EventModel;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class GetEventsController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Get(
        path: '/events',
        description: 'Get all events that are subscribable.',
        summary: 'Display a list of current events that the user can subscribe.',
        security: [
            [
                'sanctum' => []
            ]
        ],
        tags: ['Events'],
        responses: [
            new Response(
                response: HttpResponse::HTTP_OK,
                description: 'A list of events.',
                content: new JsonContent(
                    properties: [
                        new Property(property: 'totalItems', type: 'int', default: '1'),
                        new Property(property: 'data', type: 'array', items: new Items(ref: EventModel::class))
                    ]
                )
            ),
            new Response(
                response: HttpResponse::HTTP_UNAUTHORIZED,
                description: 'Not authenticated.'
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $params = $request->query();

        $validation = GetEventsControllerInputs::validateParams($params);
        if ($validation->fails()) return parent::responseBadRequest(['code' => 0]);

        $pagination = $this->repository->getAll();
        return parent::responseOkJson($pagination->toJson());
    }
}
