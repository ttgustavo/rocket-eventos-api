<?php

namespace Tests\Feature\Controller\Api\Admin\Events;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Repository\EventRepository;
use App\Presenter\Http\Controllers\Api\Admin\Events\EventControllerInputs;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class UpdateEventControllerTest extends TestCase
{
    use AuthHelperTrait;

    private EventRepository|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-01T15:00:00Z');

        $this->authAsAdminAndSuper();

        $this->repository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->instance(EventRepository::class, $this->repository);
    }

    public function test_when_updated_returns_status_code_ok_with_event_json(): void
    {
        $event = $this->createEvent();
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->method('update')->willReturn($event);

        $data = [EventControllerInputs::FIELD_SLUG => 'my-event-updated'];
        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure(['name', 'slug', 'details']);
    }

    public function test_when_is_updating_with_admin_user_returns_status_code_ok_with_event_json(): void
    {
        $event = $this->createEvent();
        $this->authAsAdmin();
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->method('update')->willReturn($event);

        $data = [EventControllerInputs::FIELD_SLUG => 'my-event-updated'];
        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure(['name', 'slug', 'details']);
    }

    public function test_when_is_updating_with_super_user_returns_status_code_ok_with_event_json(): void
    {
        $event = $this->createEvent();
        $this->authAsSuper();
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->method('update')->willReturn($event);

        $data = [EventControllerInputs::FIELD_SLUG => 'my-event-updated'];
        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure(['name', 'slug', 'details']);
    }

    public function test_when_updated_same_value_returns_status_code_no_content_with_empty_data(): void
    {
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->method('update')->willReturn(null);

        $data = [EventControllerInputs::FIELD_SLUG => 'my-event-updated'];
        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertNoContent();
    }

    public function test_when_updated_with_empty_values_returns_status_code_no_content_with_empty_data(): void
    {
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->method('update')->willReturn(null);

        $response = parent::patchJson("/api/admin/events/1");

        $response->assertNoContent();
    }

    public function test_when_event_does_not_exists_returns_status_code_bad_request_with_code_1(): void
    {
        $this->repository->method('hasEventWithId')->willReturn(false);

        $data = [EventControllerInputs::FIELD_SLUG => 'my-event-updated'];
        $response = parent::patchJson('/api/admin/events/1', $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_updating_event__with_user_not_admin_or_super_returns_status_code_forbidden(): void
    {
        $this->authAsUser();
        $this->repository->expects($this->never())->method('hasEventWithId');

        $data = [EventControllerInputs::FIELD_SLUG => 'my-event-updated'];
        $response = parent::patchJson('/api/admin/events/1', $data);

        $response->assertForbidden();
    }

    // ---- Validation
    public function test_when_one_field_is_invalid_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [
            EventControllerInputs::FIELD_NAME => 'My Event Updated',
            EventControllerInputs::FIELD_SLUG => 'my-event-updated',
            EventControllerInputs::FIELD_DETAILS => 'My event details here.',
            EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2024-01-01T00:00:00Z',
            EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2024-01-01T00:00:00Z',
            EventControllerInputs::FIELD_PRESENTATION_AT => '2024-01-01T00:00:00Z',
            'status' => -1,
        ];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_event_id_is_not_a_number_returns_status_code_bad_request_with_code_0(): void
    {
        $response = parent::patch("/api/admin/events/abc");

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_name_is_empty_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_NAME => ''];

        $response = parent::patchJson('/api/admin/events/9541', $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_name_is_less_than_minimum_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_NAME => 'Abc'];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_slug_is_empty_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_SLUG => ''];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_slug_is_less_than_minimum_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_SLUG => 'abc'];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_details_is_empty_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_DETAILS => ''];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_details_is_longer_than_maximum_returns_status_code_bad_request_with_code_0(): void
    {
        $details = '';
        $details = str_pad($details, 1001, 'a');
        $data = [EventControllerInputs::FIELD_DETAILS => $details];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_subscription_date_start_is_empty_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => ''];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_subscription_date_start_year_less_than_2020_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_SUBSCRIPTION_START_AT => '2019-03-01T15:00:00Z'];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_subscription_date_end_year_less_than_2020_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_SUBSCRIPTION_END_AT => '2019-03-01T15:00:00Z'];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_presentation_at_year_less_than_2020_returns_status_code_bad_request_with_code_0(): void
    {
        $data = [EventControllerInputs::FIELD_PRESENTATION_AT => '2019-03-01T15:00:00Z'];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_status_is_empty_returns_status_code_bad_request_with_code_0(): void
    {
        $data = ['status' => -1];

        $response = parent::patchJson("/api/admin/events/1", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_when_status_is_not_valid_returns_status_code_bad_request_with_code_0(): void
    {
        $data1 = ['status' => 6];
        $data2 = ['status' => -1];

        $response1 = parent::patchJson("/api/admin/events/1", $data1);
        $response2 = parent::patchJson("/api/admin/events/1", $data2);

        $response1->assertBadRequest();
        $response1->assertJson(['code' => 0]);
        $response2->assertBadRequest();
        $response2->assertJson(['code' => 0]);
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
