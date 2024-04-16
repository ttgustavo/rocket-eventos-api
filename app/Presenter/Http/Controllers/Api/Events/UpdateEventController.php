<?php

namespace App\Presenter\Http\Controllers\Api\Events;

use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use App\Presenter\Rules\SlugValidationRule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateEventController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $jsonString = $request->getContent();
        $json = json_decode($jsonString, true);

        $areParamsValid = $this->validateParams($eventId);
        if ($areParamsValid === false) return parent::responseBadRequest(['code' => 0]);

        $areInputsValid = $this->validateInputs($json);
        if ($areInputsValid === false) return parent::responseBadRequest(['code' => 0]);

        $areDatesValid = $this->validateDates($json);
        if ($areDatesValid === false) return parent::responseBadRequest(['code' => 0]);

        $doesEventExists = $this->repository->hasEventWithId($eventId);
        if ($doesEventExists === false) return parent::responseBadRequest(['code' => 1]);

        $event = $this->repository->update($eventId, $json);

        $hasNoChanges = $event === null;
        if ($hasNoChanges) return parent::responseOk();

        return parent::responseOk($event);
    }

    private function validateParams(int $id): bool
    {
        $inputs = ['id' => $id];
        $rules = [
            'id' => ['required', 'int', 'min:0'],
        ];

        return Validator::make($inputs, $rules)->passes();
    }

    private function validateInputs(array $inputs): bool
    {
        $rules = [
            'name' => ['nullable', 'string', 'filled', 'min:5', 'max:100'],
            'slug' => ['nullable', 'string', 'filled', 'min:4', 'max:100', new SlugValidationRule],
            'details' => ['nullable', 'string', 'filled', 'max:1000'],
            'subscription_date_start' => ['nullable', 'string', 'filled', 'date_format:Y-m-d\TH:i:sp'],
            'subscription_date_end' => ['nullable', 'string', 'filled', 'date_format:Y-m-d\TH:i:sp'],
            'presentation_at' => ['nullable', 'string', 'filled', 'date_format:Y-m-d\TH:i:sp'],
            'status' => ['nullable', 'int', 'min:0', 'max:4']
        ];
        return Validator::make($inputs, $rules)->passes();
    }

    private function validateDates(array $inputs): bool
    {
        $hasSubscriptionDateStart = key_exists('subscription_date_start', $inputs);
        $hasSubscriptionDateEnd = key_exists('subscription_date_end', $inputs);
        $hasPresentationAt = key_exists('presentation_at', $inputs);

        if ($hasSubscriptionDateStart) {
            $date = Carbon::parse($inputs['subscription_date_start']);
            $isValid = $date->year > 2020;
            if ($isValid === false) return false;
        }
        if ($hasSubscriptionDateEnd) {
            $date = Carbon::parse($inputs['subscription_date_end']);
            $isValid = $date->year > 2020;
            if ($isValid === false) return false;
        }
        if ($hasPresentationAt) {
            $date = Carbon::parse($inputs['presentation_at']);
            $isValid = $date->year > 2020;
            if ($isValid === false) return false;
        }

        return true;
    }
}
