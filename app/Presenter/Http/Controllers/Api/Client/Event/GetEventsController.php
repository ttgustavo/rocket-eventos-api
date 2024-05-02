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
        description: 'Get all events that is subscribable.',
        summary: 'Display a list of current events.',
        security: ['bearerAuth' => null],
        tags: ['Events'],
        responses: [
            new Response(
                response: HttpResponse::HTTP_OK,
                description: 'A list of events.',
                content: new JsonContent(type: 'array', items: new Items(ref: EventModel::class))
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $params = $request->query();
        var_dump($params);

        $validation = GetEventsControllerInputs::validateParams($params);
        if ($validation->fails()) return parent::responseBadRequest(['code' => 0]);

        $pagination = $this->repository->getAll();

        var_dump($pagination->totalItems);

        return parent::responseOkJson($pagination->toJson());
    }
}
