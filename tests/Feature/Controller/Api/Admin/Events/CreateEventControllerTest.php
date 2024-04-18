<?php

namespace Tests\Feature\Controller\Api\Admin\Events;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\Admin\Events\EventControllerInputs;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class CreateEventControllerTest extends TestCase
{
    use AuthHelperTrait;

    private EventRepository|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-01T00:00:00Z');

        $this->authAsAdminAndSuper();

        $this->repository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->instance(EventRepository::class, $this->repository);
    }

    public function test_when_event_is_created_returns_status_code_created_with_event_json(): void
    {
        $event = $this->createEvent('My Event', 'my-event');
        $this->repository->method('hasEventWithSlug')->willReturn(false);
        $this->repository->method('create')->willReturn($event);

        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-02T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-04-13T12:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        $response = parent::postJson('/api/admin/events', $data);

        $response->assertCreated();
        $response->assertJsonIsObject();
        $response->assertJson(['name' => 'My Event', 'slug' => 'my-event']);
    }

    public function test_when_event_is_created_by_admin_user_returns_status_code_created_with_event_json(): void
    {
        $this->authAsAdmin();

        $event = $this->createEvent('My Event', 'my-event');
        $this->repository->method('hasEventWithSlug')->willReturn(false);
        $this->repository->method('create')->willReturn($event);

        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-02T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-04-13T12:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        $response = parent::postJson('/api/admin/events', $data);

        $response->assertCreated();
        $response->assertJsonIsObject();
        $response->assertJson(['name' => 'My Event', 'slug' => 'my-event']);
    }

    public function test_when_event_is_created_by_super_user_returns_status_code_created_with_event_json(): void
    {
        $this->authAsSuper();

        $event = $this->createEvent('My Event', 'my-event');
        $this->repository->method('hasEventWithSlug')->willReturn(false);
        $this->repository->method('create')->willReturn($event);

        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-02T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-04-13T12:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        $response = parent::postJson('/api/admin/events', $data);

        $response->assertCreated();
        $response->assertJsonIsObject();
        $response->assertJson(['name' => 'My Event', 'slug' => 'my-event']);
    }

    public function test_when_creating_event_with_existing_slug_returns_status_code_bad_request(): void
    {
        $this->repository->method('hasEventWithSlug')->willReturn(true);

        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-02T12:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-04-13T12:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        $response = parent::postJson('/api/admin/events', $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_creating_event_with_user_not_admin_or_super_returns_status_code_forbidden(): void
    {
        Sanctum::actingAs(UserFactory::new()->makeOne());

        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-02T12:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-04-13T12:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];
        $response = parent::postJson('/api/admin/events', $data);

        $response->assertForbidden();
    }

    // ---- Validation
    public function test_when_creating_event_with_subscription_date_start_before_than_today_returns_status_code_bad_request_with_code_0(): void
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

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_creating_event_with_subscription_date_end_before_than_today_returns_status_code_bad_request(): void
    {
        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event',
            EventControllerInputs::FIELD_SLUG => 'my-event',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-22T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-01-01T00:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-04-13T12:00:00Z',
        ];

        $response = parent::postJson('/api/admin/events', $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_creating_event_with_subscription_date_end_before_than_start_returns_status_code_bad_request(): void
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

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_creating_event_with_presentation_at_is_before_than_now_returns_status_code_bad_request(): void
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

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    private function createEvent(string $name = '', string $slug = '', string $details = ''): EventModel
    {
        return new EventModel(
            1,
            $name,
            $slug,
            $details,
            Carbon::now(),
            Carbon::now()->addHour(),
            Carbon::now()->addDay(),
            Carbon::now(),
            Carbon::now(),
            EventStatus::Draft
        );
    }
}
