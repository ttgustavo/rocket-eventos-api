<?php

namespace App\Presenter\Http\Controllers\Api\Events;

use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use App\Presenter\Rules\SlugValidationRule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeleteEventController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

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
