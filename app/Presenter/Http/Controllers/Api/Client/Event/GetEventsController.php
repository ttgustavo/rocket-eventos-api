<?php

namespace App\Presenter\Http\Controllers\Api\Client\Event;

use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEventsController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $params = $request->query();

        $validation = GetEventsControllerInputs::validateParams($params);
        if ($validation->fails()) return parent::responseBadRequest(['code' => 0]);

        $pagination = $this->repository->getAll();

        return parent::responseOkJson($pagination->toJson());
    }
}
