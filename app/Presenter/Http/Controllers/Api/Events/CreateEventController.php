<?php

namespace App\Presenter\Http\Controllers\Api\Events;

use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use App\Presenter\Rules\DateTimeAfterNowRule;
use App\Presenter\Rules\SlugValidationRule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreateEventController extends ApiController
{
    private $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

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
        $hasSlugInRequest = array_key_exists('slug', $json);
        if ($hasSlugInRequest) {
            $slug = $json['slug'];
        } else {
            $slug = $this->generateSlugFromString($json['name']);
        }

        $eventExists = $this->repository->hasEventWithSlug($slug);
        if ($eventExists) {
            $payload = ['code' => 1];
            return parent::responseBadRequest($payload);
        }

        $details = '';
        if (key_exists('details', $json)) {
            $details = $json['details'];
        }

        $event = $this->repository->create(
            $json['name'],
            $slug,
            $details,
            $json['subscription_date_start'],
            $json['subscription_date_end'],
            $json['presentation_at']
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
            'name' => ['required', 'min:5', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', new SlugValidationRule],
            'details' => ['nullable', 'string', 'max:1000'],
            'subscription_date_start' => ['required', 'string', 'date_format:Y-m-d\TH:i:sp', new DateTimeAfterNowRule],
            'subscription_date_end' => ['required', 'string', 'date_format:Y-m-d\TH:i:sp', new DateTimeAfterNowRule],
            'presentation_at' => ['required', 'string', 'date_format:Y-m-d\TH:i:sp'],
        ];
        return Validator::make($inputs, $rules);
    }

    private function validateDates(array $input): bool
    {
        $dateTimeNow = Carbon::now();

        $dateTimeSubscriptionStartRaw = $input['subscription_date_start'];
        $dateTimeSubscriptionStart = Carbon::parse($dateTimeSubscriptionStartRaw);

        $dateTimeSubscriptionEndRaw = $input['subscription_date_end'];
        $dateTimeSubscriptionEnd = Carbon::parse($dateTimeSubscriptionEndRaw);

        $dateTimePresentationAtRaw = $input['presentation_at'];
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