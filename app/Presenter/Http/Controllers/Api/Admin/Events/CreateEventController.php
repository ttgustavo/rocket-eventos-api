<?php

namespace App\Presenter\Http\Controllers\Api\Admin\Events;

use App\Domain\Model\EventModel;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use App\Presenter\Rules\DateTimeAfterNowRule;
use App\Presenter\Rules\SlugValidationRule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CreateEventController extends ApiController
{
    private EventRepository $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Post(
        path: '/admin/events',
        summary: 'Creates an event.',
        security: [[
            'sanctum' => []
        ]],
        requestBody: new RequestBody(
            content: new JsonContent(
                required: [
                    EventControllerInputs::FIELD_NAME,
                    EventControllerInputs::FIELD_SLUG,
                    EventControllerInputs::FIELD_DETAILS,
                    EventControllerInputs::FIELD_SUBSCRIPTION_START_AT,
                    EventControllerInputs::FIELD_SUBSCRIPTION_END_AT,
                    EventControllerInputs::FIELD_PRESENTATION_AT,
                ],
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
        responses: [
            new Response(
                response: HttpResponse::HTTP_CREATED,
                description: 'The event was created and returns all its data.',
                content: new JsonContent(ref: EventModel::class)
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
        $jsonString = $request->getContent();
        $json = json_decode($jsonString, true);

        $validator = $this->validateInputs($json);
        if ($validator->fails()) {
            $payload = ['code' => 0];
            return parent::responseBadRequest($payload);
        }

        $areDatesValid = $this->validateDates($json);
        if ($areDatesValid === false) {
            $payload = ['code' => 0];
            return parent::responseBadRequest($payload);
        }

        $slug = '';
        $hasSlugInRequest = array_key_exists(EventControllerInputs::FIELD_SLUG, $json);
        if ($hasSlugInRequest) {
            $slug = $json[EventControllerInputs::FIELD_SLUG];
        } else {
            $slug = $this->generateSlugFromString($json[EventControllerInputs::FIELD_NAME]);
        }

        $eventExists = $this->repository->hasEventWithSlug($slug);
        if ($eventExists) {
            $payload = ['code' => 1];
            return parent::responseBadRequest($payload);
        }

        $details = '';
        if (key_exists(EventControllerInputs::FIELD_DETAILS, $json)) {
            $details = $json[EventControllerInputs::FIELD_DETAILS];
        }

        $event = $this->repository->create(
            $json[EventControllerInputs::FIELD_NAME],
            $slug,
            $details,
            $json[EventControllerInputs::FIELD_SUBSCRIPTION_START_AT],
            $json[EventControllerInputs::FIELD_SUBSCRIPTION_END_AT],
            $json[EventControllerInputs::FIELD_PRESENTATION_AT]
        );

        return parent::responseCreated($event);
    }

    private function generateSlugFromString($name): string
    {
        // Replace spaces to dashes
        $slug = preg_replace('/[\s]/i', '-', $name);

        // Replace two or more followed dashes to one dash.
        $slug = preg_replace('/[-]{2,}/i', '-', $slug);

        return strtolower($slug);
    }

    private function validateInputs(array $inputs): \Illuminate\Validation\Validator
    {
        $rules = [
            EventControllerInputs::FIELD_NAME => ['required', 'min:5', 'max:100'],
            EventControllerInputs::FIELD_SLUG => ['nullable', 'string', 'max:100', new SlugValidationRule],
            EventControllerInputs::FIELD_DETAILS => ['nullable', 'string', 'max:1000'],
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => ['required', 'string', 'date_format:Y-m-d\TH:i:sp', new DateTimeAfterNowRule],
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => ['required', 'string', 'date_format:Y-m-d\TH:i:sp', new DateTimeAfterNowRule],
            EventControllerInputs::FIELD_PRESENTATION_AT => ['required', 'string', 'date_format:Y-m-d\TH:i:sp'],
        ];

        return Validator::make($inputs, $rules);
    }

    private function validateDates(array $input): bool
    {
        $dateTimeNow = Carbon::now();

        $dateTimeSubscriptionStartRaw = $input[EventControllerInputs::FIELD_SUBSCRIPTION_START_AT];
        $dateTimeSubscriptionStart = Carbon::parse($dateTimeSubscriptionStartRaw);

        $dateTimeSubscriptionEndRaw = $input[EventControllerInputs::FIELD_SUBSCRIPTION_END_AT];
        $dateTimeSubscriptionEnd = Carbon::parse($dateTimeSubscriptionEndRaw);

        $dateTimePresentationAtRaw = $input[EventControllerInputs::FIELD_PRESENTATION_AT];
        $dateTimePresentationAt = Carbon::parse($dateTimePresentationAtRaw);

        if ($dateTimeSubscriptionEnd->isBefore($dateTimeSubscriptionStart)) {
            return false;
        }
        if ($dateTimePresentationAt->isBefore($dateTimeNow)) {
            return false;
        }

        return true;
    }
}
