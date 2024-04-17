<?php

namespace Tests\Feature\Controller\Api\Events;

use App\Presenter\Http\Controllers\Api\Events\EventControllerInputs;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CreateEventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_status_code_created_when_event_is_created(): void
    {
        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-02T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-04-13T12:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        Carbon::setTestNow('2024-01-01T15:00:00Z');

        $response = parent::postJson('/api/admin/events', $data);

        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function test_returns_status_code_bad_request_when_creating_event_with_same_slug(): void
    {
        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-02T12:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-04-13T12:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        Carbon::setTestNow('2024-01-01T15:00:00Z');

        parent::postJson('/api/admin/events', $data);
        $response = parent::postJson('/api/admin/events', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 1]);
    }

    public function test_returns_status_code_bad_request_when_creating_event_with_subscription_date_start_before_than_today(): void
    {
        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-01T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-01-11T00:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        Carbon::setTestNow('2024-01-01T15:00:00Z');

        $response = parent::postJson('/api/admin/events', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_when_creating_event_with_subscription_date_end_before_than_today(): void
    {
        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-22T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-01-01T00:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        Carbon::setTestNow('2024-01-03T15:00:00Z');

        $response = parent::postJson('/api/admin/events', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_when_creating_event_with_subscription_date_end_before_than_start(): void
    {
        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-22T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-01-21T00:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        Carbon::setTestNow('2024-01-03T15:00:00Z');

        $response = parent::postJson('/api/admin/events', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_when_creating_event_with_presentation_at_is_before_than_now(): void
    {
        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-22T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-01-23T00:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-01-01T00:00:00Z',
        ];
        Carbon::setTestNow('2024-01-02T15:00:00Z');

        $response = parent::postJson('/api/admin/events', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 0]);
    }
}
