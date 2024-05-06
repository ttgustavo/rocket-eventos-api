<?php

namespace App\Presenter\Http\Controllers\Api\Admin\Events;

use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEventsAdminController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

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
