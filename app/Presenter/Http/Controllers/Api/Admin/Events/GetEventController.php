<?php

namespace App\Presenter\Http\Controllers\Api\Admin\Events;

use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use App\Presenter\Rules\SlugValidationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** This controller is for admin only. */
class GetEventController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

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
