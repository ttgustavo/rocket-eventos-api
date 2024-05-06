<?php

namespace App\Presenter\Http\Controllers\Api\Admin\Events;

use App\Domain\Model\EventModel;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class GetEventsAdminController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Get(
        path: '/admin/events',
        summary: 'List events.',
        security: [[
            'sanctum' => []
        ]],
        tags: ['Admin'],
        parameters: [
            new Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                schema: new Schema(type: 'integer')
            )
        ],
        responses: [
            new Response(
                response: HttpResponse::HTTP_OK,
                description: 'The event was created and returns all its data.',
                content: new JsonContent(ref: EventModel::class)
            ),
            new Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'When "code" is zero, means validation.',
                content: new JsonContent(
                    properties: [
                        new Property(property: 'totalItems', type: 'int', default: '1'),
                        new Property(property: 'data', type: 'array', items: new Items(ref: EventModel::class))
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
    public function __invoke(Request $request): JsonResponse
    {
        $params = $request->query();
        $page = 1;

        if (key_exists(GetEventsAdminInputs::PARAM_PAGE, $params)) {
            $validation = GetEventsAdminValidation::validate($params);
            if ($validation->fails()) {
                return parent::responseBadRequest(['code' => 0]);
            }

            $data = $validation->valid();
            $page = $data[GetEventsAdminInputs::PARAM_PAGE];
        }

        $pagination = $this->repository->list($page);
        return parent::responseOkJson($pagination->toJson());
    }
}
