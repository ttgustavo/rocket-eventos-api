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

    public function __invoke(Request $request, int|string $eventId): JsonResponse
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

    private function validateParams(int|string $id): bool
    {
        $inputs = [EventControllerInputs::FIELD_ID => $id];
        $rules = [
            EventControllerInputs::FIELD_ID => ['required', 'int', 'min:0'],
        ];

        return Validator::make($inputs, $rules)->passes();
    }

    private function validateInputs(array $inputs): bool
    {
        $rules = [
            EventControllerInputs::FIELD_NAME => ['nullable', 'string', 'filled', 'min:5', 'max:100'],
            EventControllerInputs::FIELD_SLUG => ['nullable', 'string', 'filled', 'min:4', 'max:100', new SlugValidationRule],
            EventControllerInputs::FIELD_DETAILS => ['nullable', 'string', 'filled', 'max:1000'],
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => ['nullable', 'string', 'filled', 'date_format:Y-m-d\TH:i:sp'],
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => ['nullable', 'string', 'filled', 'date_format:Y-m-d\TH:i:sp'],
            EventControllerInputs::FIELD_PRESENTATION_AT => ['nullable', 'string', 'filled', 'date_format:Y-m-d\TH:i:sp'],
            EventControllerInputs::FIELD_STATUS => ['nullable', 'int', 'min:0', 'max:4']
        ];
        return Validator::make($inputs, $rules)->passes();
    }

    private function validateDates(array $inputs): bool
    {
        $hasSubscriptionDateStart = key_exists(EventControllerInputs::FIELD_SUBSCRIPTION_START_AT, $inputs);
        $hasSubscriptionDateEnd = key_exists(EventControllerInputs::FIELD_SUBSCRIPTION_END_AT, $inputs);
        $hasPresentationAt = key_exists(EventControllerInputs::FIELD_PRESENTATION_AT, $inputs);

        if ($hasSubscriptionDateStart) {
            $date = Carbon::parse($inputs[EventControllerInputs::FIELD_SUBSCRIPTION_START_AT]);
            $isValid = $date->year > 2020;
            if ($isValid === false) return false;
        }
        if ($hasSubscriptionDateEnd) {
            $date = Carbon::parse($inputs[EventControllerInputs::FIELD_SUBSCRIPTION_END_AT]);
            $isValid = $date->year > 2020;
            if ($isValid === false) return false;
        }
        if ($hasPresentationAt) {
            $date = Carbon::parse($inputs[EventControllerInputs::FIELD_PRESENTATION_AT]);
            $isValid = $date->year > 2020;
            if ($isValid === false) return false;
        }

        return true;
    }
}
