<?php

namespace App\Presenter\Http\Controllers\Api\Admin\Events;

use App\Domain\Model\EventModel;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use App\Presenter\Rules\SlugValidationRule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Patch;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UpdateEventController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Patch(
        path: '/admin/events/{id}',
        summary: 'Updates an event.',
        security: [[
            'sanctum' => []
        ]],
        requestBody: new RequestBody(
            content: new JsonContent(
                properties: [
                    new Property(property: EventControllerInputs::FIELD_NAME, type: 'string', default: ''),
                    new Property(property: EventControllerInputs::FIELD_SLUG, type: 'string', default: ''),
                    new Property(property: EventControllerInputs::FIELD_DETAILS, type: 'string', default: ''),
                    new Property(property: EventControllerInputs::FIELD_SUBSCRIPTION_START_AT, type: 'string', format: 'date-time', default: ''),
                    new Property(property: EventControllerInputs::FIELD_SUBSCRIPTION_END_AT, type: 'string', format: 'date-time', default: ''),
                    new Property(property: EventControllerInputs::FIELD_PRESENTATION_AT, type: 'string', format: 'date-time', default: ''),
                ]
            )
        ),

        tags: ['Admin'],
        parameters: [
            new Parameter(
                name: 'id',
                description: 'The id of the event.',
                in: 'path',
                required: true,
                schema: new Schema(type: 'integer'))
        ],
        responses: [
            new Response(
                response: HttpResponse::HTTP_OK,
                description: 'The event was updated and returns all its data.',
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
        if ($hasNoChanges) return parent::responseNoContent();

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
            EventControllerInputs::FIELD_STATUS => ['nullable', 'int', 'min:0', 'max:5']
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
